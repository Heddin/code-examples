<?php

/**
 * Created by Heddin.
 */

namespace App\Http\Controllers\Admin\Users;

use App\Events\Interfaces\IUserActionEvent;
use App\Model\Course;
use App\Model\ImportedUser;
use App\Model\Progress;
use App\Model\Categories\Category;
use App\Model\UserActions\UserRegistered;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsersController extends Controller
{

    /**
     * shows Admin\Users\Dashboard sub-page
     * @return View
     */
    public function index()
    {

        $categories = Category::with('courses')->get();

        foreach ($categories as $cat) {

            $cat->usersCount = 0;

            foreach ($cat->courses as $course) {
                $course->usersCount = $course->getSubscribersCount();
                $course->averageProgress = Progress::getAverageProgress($course);
                $cat->usersCount += $course->usersCount;
            }
        }

        return view('admin.partials.users.dashboard')->with([
            'categories' => $categories
        ]);
    }
    /**
     * shows Admin\Users\Find sub-page
     * @return View
     */
    public function find()
    {
        return view('admin.partials.users.find');
    }
    /**
     * shows Admin\Users\Import sub-page
     * @return View
     */
    public function import()
    {
        return view('admin.partials.users.import-form');
    }
    /**
     * shows Admin\Users\Action sub-page
     * @return View
     */
    public function actions()
    {

        $actions = [
            IUserActionEvent::ACTION_REGISTERED => 'Зарегистрировано',
            IUserActionEvent::ACTION_PURCHASED => 'Приобретено',
            IUserActionEvent::ACTION_PROGRESS => 'Прогресс'
        ];


        return view('admin.partials.users.actions')->with(compact('actions'));
    }

    /**
     * renders HTML foe "options"-section on Users\Actions page
     * depends on @param $action
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     * @throws \Throwable
     */
    public function render_options($action)
    {
        switch ((int)$action) {

            case IUserActionEvent::ACTION_REGISTERED:
                {
                    return view('admin.partials.users.options.registered')->render();
                }
                break;
            case IUserActionEvent::ACTION_PROGRESS:
                {
                    return view('admin.partials.users.options.progress');
                } break;
            case IUserActionEvent::ACTION_PURCHASED:
                {
                    return view('admin.partials.tb-alert')->with([
                        'status' => 'warning',
                        'message' => "В разработке"
                    ])->render();
                    //return view('admin.partials.users.options.purchased')->render();
                }
                break;

            default:
                {
                    return view('admin.partials.tb-alert')->with([
                        'status' => 'danger',
                        'message' => "No such action registered"
                    ])->render();
                }
        }

    }

    /**
     * gathers and send data, requested on Users\Actions page
     * depends on @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|null|string
     * @throws \Throwable
     */
    public function get_build_data(Request $request)
    {

        $params = $request->all();

        switch ($params['actions']) {

            case IUserActionEvent::ACTION_REGISTERED:
                {

                    $build_data = new \stdClass();

                    $data = $this->handleRegisteredDates($params);


                   if (isset($params['summary'])){
                        $build_data->all = $data->users;
                   }
                   if (isset($params['standard'])){
                        $build_data->standard_form = $data->standard;
                   }
                   if(isset($params['vk'])){
                        $build_data->vk = $data->social['vk'];
                   }
                   if(isset($params['fb'])){
                        $build_data->fb = $data->social['fb'];
                   }
                   if(isset($params['gp'])){
                        $build_data->gp = $data->social['google'];
                   }

                   return response()->json($build_data);

                }
                break;

            case IUserActionEvent::ACTION_PURCHASED:
                {
                    return null;
                }
                break;

            default:
                {
                    return view('admin.partials.tb-alert')->with([
                        'status' => 'danger',
                        'message' => "No such action registered"
                    ])->render();
                }
        }


    }

    /**
     * searching User::class object sends rendered HTML to Users\Find page
     * depends @param Request $request
     *
     * @return string
     * @throws \Throwable
     */
    public function find_user(Request $request)
    {
        $user = User::search($request->search_query);

        if (!$user) {
            return "Not found";
        }
        return $this->renderUserInfo($user);
    }

    /**
     * Renders parsed data from ReaderCSV into HTML
     * on Users\Import page
     * depends on  @param Request $request
     *
     * @return string
     * @throws \Throwable
     */
    public function render_import(Request $request)
    {

        $rawData = $request->all();
        $users = [];
        foreach ($rawData as $rd) {
            $user = [];
            foreach ($rd as $k => $v) {
                $user[trim($k)] = $v;
            }
            $users[] = $user;
        }
        try {
            $response = view('admin.partials.users.import')->with(compact('users'))->render();
        } catch (\Exception $e) {
            $response = view('admin.partials.tb-alert')->with([
                'status' => 'danger',
                'message' => "Probably bad CSV, make sure that it has two columns and first row contains only 'email' and 'name'; Error: " . $e->getMessage()
            ])->render();
        }
        return $response;
    }


    /**
     * creating users with given  @param Request $request,
     * stores to DB, assigning Excel-basic course to them.
     *
     * @return string - report about changes it made;
     * @throws \Throwable
     */
    public function import_users(Request $request)
    {

        $users = [
            'Imported' => 0,
            'Already_Imported' => 0,
            'Registered' => 0,
            'Already_Registered' => 0,
            'Subscribed_to_Excelbas' => 0,
            'Already_Subscribed_to_Excelbas' => 0,
        ];


        foreach ($request->all() as $user) {

            $iUser = ImportedUser::where('email', $user['email'])->first();
            if (!$iUser) {
                $iUser = new ImportedUser($user);
                $users['Imported']++;
            } else {
                $users['Already_Imported']++;
            }
            $iUser->save();

            $aUser = User::where("email", $user['email'])->first();
            if (!$aUser) {
                $aUser = new User($user);
                $user->password = Hash::make('step21expert');
                $users['Registered']++;
                $users['Already_Registered']++;
            }
            $aUser->save();

            $course = Course::findOrFail(4);

            if($aUser->can('buy', $course))
            {
	            $aUser->courses()->attach($course->id);

	            $users['Subscribed_to_Excelbas']++;
            }else{
	            $users['Already_Subscribed_to_Excelbas']++;
            }
        }


        $view = view('admin.partials.users.after-import')->with(compact('users'))->render();

        return $view;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function add_course(Request $request)
    {
        $response = new \stdClass();
        $message = "Добавлено:\n\r";

        $user = User::findOrFail($request->user_id);

        try {
            $course = Course::findOrFail($request->course);

            if ($request->course) {

            	if($user->can('buy', $course))
            	{
		            $user->courses()->attach($course->id);
	            }

                $message .= "{$course->name}\n\r";
            }
            if ($request->sim) {
                $supplement = $course->getSimulationAndTests();

	            if($user->can('buy', $supplement))
	            {
		            $user->supplements()->attach($supplement->id,
			            [
			            	'course_id' => $course->id,
				            'payment_status' => 'success',
				            'type' => 0
			            ]);
	            }

                $message .= "+ Симуляции\n\r";
            }
            if ($request->exam) {
                $supplement = $course->getExaminationSupplement();

	            if($user->can('buy', $supplement))
	            {
		            $user->supplements()->attach($supplement->id,
			            [
				            'course_id' => $course->id,
				            'payment_status' => 'success',
				            'type' => 1
			            ]);
	            }

                $message .= "+ Экзамен \n\r";
            }

            $status = "success";

        } catch (\Exception $e) {
            $status = 'danger';
            $message = $e->getMessage() . PHP_EOL . " {$e->getFile()}" . PHP_EOL . " {$e->getLine()}";
        }

        $courses = Course::all()->whereNotIn('id', $user->getUserCoursesId())
            ->filter(function ($course) {
                $course->sim = $course->haveSimulationAndTests();
                $course->exam = $course->haveExamination();
                return true;
            });

        $response->stats = $this->renderUserInfo($user);

        $response->message = view('admin.partials.tb-alert')->with([
            'status' => $status,
            'message' => $message
        ])->render();


        return response()->json($response);
    }

    /**
     * utility method for find_user()
     * gathers info about given @param $user (User::class)
     * and renders HTML;
     *
     * @return string << rendered HTML
     * @throws \Throwable
     */
    private function renderUserInfo($user)
    {
	    $user->load('orders');

        $courses = Course::all()->whereNotIn('id', $user->getUserCoursesId())
            ->filter(function ($course) {
                $course->sim = $course->haveSimulationAndTests();
                $course->exam = $course->haveExamination();
                return true;
            });

        return view('admin.partials.users.search')->with([
            'user' => $user,
            'courses' => $courses,
            'orders' => $user->orders->unique('name')
        ])->render();
    }

    /**
     *  handles data organisation in date bounds;
     *
     * @param array $params - array of request props from get_build_data();
     *
     * @return \stdClass $data - contains organized data;
     */
    private function handleRegisteredDates(array $params)
    {

        $data = new \stdClass();

        if (isset($params['to-date'])) {
            if (isset($params['from-date'])) {
                $data->users = User::whereBetween('created_at',[$params['from-date'], $params['to-date']])->get();
                $data->social = UserRegistered::whereBetween('date',[$params['from-date'], $params['to-date']])->get();
            } else {
                $data->users = User::where('created_at', '<=', $params['to-date'])
                    ->get();
                $data->social = UserRegistered::where('date', '<=', $params['to-date'])
                    ->get();
            }
        } else if (isset($params['from-date'])) {
            $data->users = User::where('created_at', '>=', $params['from-date'])
                ->get();
            $data->social = UserRegistered::where('date', '>=', $params['from-date'])
                ->get();
        } else {

           $default_date_end = Carbon::now();
           $default_date_start = Carbon::now()->subMonths(3);


           $data->users = User::whereBetween('created_at',[$default_date_start, $default_date_end])->get();
           $data->social = UserRegistered::whereBetween('date', [$default_date_start,$default_date_end])->get();
        }

        $dates = $data->users->pluck('created_at');
        $formattedDates = [];
        foreach($dates as $date){
            $formattedDates[] = $date->format('Y-m-d');
        }
        $dates = array_unique($formattedDates);
        sort($dates);

        $users = [];
        $standard = [];
        $social = [];
        $social['vk'] = [];
        $social['fb'] = [];
        $social['google'] = [];

        foreach ($dates as $date){
            $users[$date] = $data->users
                                         ->where('created_at','>=', Carbon::parse($date))
                                         ->where('created_at','<=',
                                             Carbon::parse($date)->addDay())->count();
            $day = $data->social->where('date', $date)->first();

            $social['vk'][$date] = $day && $day->vk_count  ? $day->vk_count : 0;
            $social['fb'][$date] = $day && $day->fb_count  ? $day->fb_count : 0;
            $social['google'][$date] = $day && $day->google_count ? $day->google_count : 0;
            $standard[$date] = $day && $day->user_count ? $day->user_count : 0;
        }

        $data->users = $users;
        $data->social = $social;
        $data->standard = $standard;

        return $data;
    }

}
