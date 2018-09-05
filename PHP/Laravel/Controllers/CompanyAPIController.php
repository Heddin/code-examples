<?php
/**
 * Created by Heddin.
 *
 * Date: 03.08.2018
 * Time: 11:19
 */

namespace App\Http\Controllers\API\Company;


use App\Company;
use App\Http\Controllers\Controller;
use App\Jobs\MailJobs;
use App\Model\Company\CompanyCourse;
use App\Model\Company\CompanyUser;
use App\Model\CompanyUserCourseAssigned;
use App\Model\CompanyUserPretestAssigned;
use App\Model\Course;
use App\Model\Invite;
use App\Model\PayedCertificate;
use App\Model\Tests\Tests;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;


/**
 * Caching time //TODO: no need for this, cache is bad idea
 */
const MINUTES = 30;
/**
 * Path to folder with Logos of companies
 */
const COMPANY_LOGO_PATH = '/assets/img/company_logos/';
/**
 * Default user position in company.
 * Used if user didn't enter his position, when joining company
 */
const DEFAULT_POSITION = "Пользователь";
/**
 * Default status for invited user ( equal to STATUS_INVITED )
 */
const DEFAULT_STATUS = 0;

/**
 * Class CompanyAPIController
 * @package App\Http\Controllers\API\Company
 *
 *  Processes requests from Vuex, at Company Cabinet
 */
class CompanyAPIController extends Controller
{
    /**
     * Determines invited user status. User invited, waiting him to join.
     */
    const STATUS_INVITED = 0;
    /**
     * Determines invited user status. User joined company.
     */
    const STATUS_ACTIVE = 1;
    /**
     * Determines invited user status. User fired from company.
     * Record about him is not deleted;
     */
    const STATUS_RELEASED = 2;

    /**
     * @var MailJobs $mailer : Object for mail services
     */
    protected $mailer;
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable | null $user
     * App/User object. Current user (Company administrator)
     *
     */
    protected $user;
    /**
     * @var Request $request : object contains current request
     */
    protected $request;

    /**
     * CompanyAPIController constructor.
     *
     * @param MailJobs $mailer
     *
     */
    public function __construct(MailJobs $mailer)
    {
        $this->mailer = $mailer;
        $this->user = Auth::user();
        $this->request = Request::capture();
    }


    /**
     * @param Company $id - DI App/Company, based on request
     *
     * Entry point, load current company with all needed relation
     * //TODO: Optimize. MB, use model `hydration` to reduce DB load and answer time
     *
     * @return string -> Json-encoded App/Company object
     */
    public function load_company(Company $id)
    {

        $company = $id;

        $company->load([
            'members.profile.courses',
            'purchased_courses.course.category',
            'assigned_pretests', 'assigned_courses'
        ]);

        $company->pretests = $company->pretests()->load('category');

        foreach ($company->pretests as $pretest) {
            $pretest->letter = $pretest->short_letter;
        }

        foreach ($company->members as $user) {
            $user->courses_count = ($user->profile) ? $user->profile->courses->count() : 0;
            $user->invite_attempts = $user->get_invite_attempts();
            $user->preliminary_tests = !($user->profile) ?: $user->profile->db_preliminary_results();

            if ($user->profile) {
                $user->profile->statistics = $user->profile->get_statistics();
            }
        }

        foreach ($company->purchased_courses as $purchased) {
            $purchased->img_src = "/assets/img/courses_" . $purchased->course->mnemo . ".png";
            $purchased->add_license_path = "/payment/courses/" . $purchased->course->mnemo . "?licenses=true";
            $purchased->course->short_letter = $purchased->course->getFirstLetter();
        }

//        foreach ($company->assigned_courses as $course){
//
//            $course->user->statistics = $course->user->get_statistics();
//            $course->user->statistics = $course->user->statistics
//                                                ->where($course->id)->first();
//        }

        $company->count_users = $company->members->count();
        $company->courses_count = $company->purchased_courses->count();

        return Response::json($company);
    }

    /**
     * @param Company $id
     *
     * Updates current App/Company, based on request
     *
     * @return string -> Json-encoded App/Company object
     */
    public function update_company(Company $id)
    {

        $data = $this->request->all();
        $company = $id;

        //if data changes doing modification, else leaving as it is;

        if (isset($data['name'])
            && !empty($data['name'])
            && $data['name'] != $company->name) {
            $company->name = $data['name'];
        }
        if (isset($data['slug'])
            && !empty($data['slug'])
            && $data['slug'] != $company->slug) {
            $company->slug = $data['slug'];
        }
        if (isset($data['email'])
            && !empty($data['email'])
            && $data['email'] != $company->email) {
            $company->email = $data['email'];
        }
        if ($this->request->hasFile('logo')) {

            $file = $this->request->file('logo');

            /* ****avoid encoding troubles******************************************/
            $filename = $file->getClientOriginalName();
            $filename = explode('.', $filename);
            $name = str_slug($filename[0]) . '.' . $file->getClientOriginalExtension();
            /***********************************************************************/

            $path = COMPANY_LOGO_PATH . $name;

            //some magic that prevents excessive file manipulation

            if ($company->logo != $path) {
                $file->move(public_path(COMPANY_LOGO_PATH), $name);
                $company->logo = $path;
            }
        }

        $company->save();

        return $this->load_company($company);
    }

    /**
     * @param Company $id
     *
     * Removes current App\Company object, removes record about company.
     *
     * //TODO: mb, soft delete?
     * //TODO: clean up all related records for this company
     *
     * @return string Redirect to `company_home` route
     * @throws \Exception
     */
    public function remove_company(Company $id)
    {

        $user = $id->users->where('pivot.position', 'Создатель')->first();
        if ($user && $user->is_company) {
            $user->is_company = false;
            $user->save();
            $id->delete();
        }

        return route('company_home');
    }

    /**
     * Sends invite letter for selected user,
     * creates record about user as company member (CompanyUser::class),
     * based on request
     *
     * @return string -> Json-encoded App/Company object
     */
    public function send_invite()
    {

        $company = Company::find($this->request->company_id);

        $user = User::getUserByEmail($this->request->email);

        $data = [
            'email' => $this->request->email,
            'companies_id' => $this->request->company_id
        ];

        $member = CompanyUser::where([
            'user_email' => $data['email'],
            'companies_id' => $data['companies_id']
        ])->exists();


        $invite = Invite::where($data)->first();

        if ($user) {
            $data['user_id'] = $user->id;
        }

        if (!$member) {
            $new_member = new CompanyUser();

            $data['status'] = DEFAULT_STATUS;
            $data['position'] = DEFAULT_POSITION;
            $data['user_email'] = $data['email'];

            $new_member->fill($data);
            $new_member->save();
        }

        if ($invite) {
            $invite->increment('attempt');
        } else {
            $invite = new Invite();
            $data['code'] = $this->generate_code();
            $invite->fill($data);
        }
        $invite->save();

        $link = route('invite_form', ['code' => $invite->code]);

        if ($invite->attempt <= Invite::MAX_ATTEMPT) {
            $this->mailer->MailInvite($this->request->email, $link, $company->name, $this->request->message);
        }

        return $this->load_company($company);
    }

    /**
     * Cancels invite for user, removes token for invite,
     * removes record about user as member of company (CompanyUser::class),
     * based on request
     *
     * @return string -> Json-encoded App/Company object
     * @throws \Exception
     */
    public function cancel_invite()
    {
        $data = $this->request->all();

        $company = Company::find($data['company_id']);
        $member = CompanyUser::find($data['member_id']);

        $invite = $member->invite;

        $invite->delete();
        $member->delete();

        return $this->load_company($company);
    }

    /**
     * Re-sends invite letter for user,
     * controls invite attempts (count <= Invite::MAX_Attempts)
     *
     * @return string -> Json-encoded App/Company object
     */
    public function resend_invite()
    {
        $data = $this->request->all();

        $company = Company::find($data['company_id']);
        $member = CompanyUser::find($data['member_id']);

        $invite = $member->invite;

        $invite->code = $this->generate_code();
        $invite->email = $this->request->email;
        $invite->increment('attempt');

        $invite->save();

        $link = route('invite_form', ['code' => $invite->code]);

        if ($invite->attempt <= Invite::MAX_ATTEMPT) {
            $this->mailer->MailInvite($this->request->email, $link, $company->name, $this->request->message);
        }

        return $this->load_company($company);
    }

    /**
     * Changes status of use as company member (CompanyUser::class)
     *
     * @return string -> Json-encoded App/Company object
     */
    public function change_member_status()
    {
        $data = $this->request->all();

        $company = Company::find($data['company_id']);
        $member = CompanyUser::find($data['member_id']);

        $current_status = $member->status;

        switch ($current_status) {
            case self::STATUS_ACTIVE :
                $member->status = self::STATUS_RELEASED;
                break;
            case self::STATUS_RELEASED :
                $member->status = self::STATUS_ACTIVE;
                break;
        }
        $member->save();

        return $this->load_company($company);
    }

    /**
     * Removes record about company member (CompanyUser::class)
     *
     * @return string -> Json-encoded App/Company object
     * @throws \Exception
     */
    public function remove_member()
    {
        $data = $this->request->all();

        $member = CompanyUser::find($data['member_id']);
        $company = Company::find($data['company_id']);

        $member->delete();

        return $this->load_company($company);

    }

    /**
     * Assigns task (course,pretest or exam) for company member (CompanyUser::class)
     *
     * if user (App\User) has no current course, adds this course to users `purchased_courses`
     * and decrements license count of Company
     *
     * Sends notification mail
     *
     * @return mixed
     */
    public function assign_task()
    {

        $data = $this->request->all();
        $company = Company::find($data['company_id']);
        $member = CompanyUser::find($data['member_id']);


        switch ($data['item_type']) {
            case 'course' :
                {
                    $course = Course::find($data['item_id']);
                    $cuca = CompanyUserCourseAssigned::firstOrNew([
                        'company_id' => $company->id,
                        'user_id' => $member->profile->id,
                        'course_id' => $course->id,
                    ]);

                    $companyCourse = CompanyCourse::where([
                        'course_id' => $course->id,
                        'company_id' => $company->id
                    ])->first();

                    $cuca->fill([
                        'company_id' => $company->id,
                        'user_id' => $member->profile->id,
                        'course_id' => $course->id
                    ]);

                    if ($companyCourse->unlim) {

                        $member->profile->add_course($course->id);
                        $member->profile->add_supplements($course->id, $company->id);

                        $userCertificate = new PayedCertificate();
                        $params = [
                            'user_id' => $member->profile->id,
                            'license_left' => null,
                            'course_id' => $course->id,
                            'count_licenses' => null,
                            'company_id' => $company->id
                        ];
                        $userCertificate->fill($params);

                        $userCertificate->save();
                    } else {
                        $licenses = $companyCourse->license_left;
                        if ($licenses) {
                            $added = $member->profile->add_course($course->id);
                            $member->profile->add_supplements($course->id);

                            $userCertificate = new PayedCertificate();
                            $params = [
                                'user_id' => $member->profile->id,
                                'license_left' => null,
                                'course_id' => $course->id,
                                'count_licenses' => null,
                                'company_id' => $company->id
                            ];
                            $userCertificate->fill($params);
                            $userCertificate->save();

                            if ($added) {
                                $companyCourse->decrement('license_left');
                            }
                        }

                    }
                    $cuca->save();

                    $url = route('getCourse', ['activeMnemo' => $course->mnemo]);


                    $message = "Компания {$company->name} назначила Вам прохождение :  {$course->name}.";
                    $message = $message . "\n\rКликните по ссылке чтобы перейти к курсу: " . $url;
                    $message = $message . "\n\rЭто письмо сформировано автоматически. Пожалуйста, не отвечайте на него.";
                    $message = $message . "\n\rЕсли у Вас есть вопросы, Вы можете обратиться по электронной почте client@skill.im.";

                    $member->profile->sendMail($message, "{$course->name}", "notificator@skill.im", "Skill.im");
                }
                break;
            case 'pretest' :
                {
                    $pretest = Tests::find($data['item_id']);

                    $cupa = CompanyUserPretestAssigned::firstOrNew([
                        'company_id' => $company->id,
                        'user_id' => $member->profile->id,
                        'pretest_id' => $pretest->id,
                    ]);

                    $cupa->fill([
                        'company_id' => $company->id,
                        'user_id' => $member->profile->id,
                        'pretest_id' => $pretest->id
                    ]);
                    $url = route('show_test', ['id' => $pretest->id]);


                    $message = "Компания {$company->name} назначила Вам прохождение : Предварительное тестирование для {$pretest->category->name}.";
                    $message = $message . "\n\rКликните по ссылке чтобы перейти к предварительному тесту: " . $url;
                    $message = $message . "\n\rЭто письмо сформировано автоматически. Пожалуйста, не отвечайте на него.";
                    $message = $message . "\n\rЕсли у Вас есть вопросы, Вы можете обратиться по электронной почте client@skill.im.";


                    $cupa->save();
                    $member->profile->sendMail($message, "Предварительное тестирование для {$pretest->category->name}", "notificator@skill.im", "Skill.im");

                }
                break;
        }

        return $this->load_company($company);
    }

    /**
     * Generate unique code, if code already exist then generate new.
     * @return string $code   Generated code
     **/
    private function generate_code()
    {
        $result = openssl_random_pseudo_bytes(16, $crypto_strong);
        $code = "";

        if (false === $result || false === $crypto_strong) {
            throw  new \RuntimeException('Code generation failed');
        } else {
            $code = bin2hex($result);
            $checkCode = (new Invite())->checkCode($code);
            while ($checkCode) {
                $result = openssl_random_pseudo_bytes(16, $crypto_strong);
                if (false === $result || false === $crypto_strong) {
                    throw  new \RuntimeException('Code generation failed');
                } else {
                    $code = bin2hex($result);
                    $checkCode = (new Invite())->checkCode($code);
                }
            }
        }

        return $code;
    }
}