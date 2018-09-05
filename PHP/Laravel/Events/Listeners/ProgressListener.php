<?php

namespace App\Listeners\Progress;

use App\Events\UserAction\Progress\CourseCompletedEvent;
use App\Events\UserAction\Progress\CourseStartedEvent;
use App\Events\UserAction\Progress\LessonCompletedEvent;
use App\Model\UserActions\ProgressUserDetail;
use Carbon\Carbon;


class ProgressListener
{

    protected $model;
    protected $user;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(){

    }

    /**
     * Handle the event.
     *
     * @param  CourseCompletedEvent  $event
     * @return void
     */
    public function course_started(CourseStartedEvent $event){
        $this->user = $event->user;

        $model = ProgressUserDetail::where('user_id',
                                   $this->user->id)->first();

        $this->model = $model ?: new ProgressUserDetail();

        $this->model->user_id = $event->user->id;

        $progress = $this->model->progress ?: [
            'course_started' => [],
            'lessons_completed' => [],
            'course_completed' => []
        ];

        $c = [
            'course_id' => $event->course->id,
            'time' => Carbon::now()->toDateTimeString()
        ];

        array_push($progress['course_started'],$c);

        $this->model->progress = $progress;
        $this->model->save();
    }

    public function course_completed(CourseCompletedEvent $event){

        $this->user = $event->user;
        $model = ProgressUserDetail::where('user_id',$this->user->id)->first();
        $this->model = $model ?: new ProgressUserDetail();

        $this->model->user_id = $event->user->id;

        $progress = $this->model->progress;
        $c = [
            'course_id' => $event->course->id,
            'time' => Carbon::now()->toDateTimeString()
        ];

        array_push($progress['course_completed'],$c);

        $this->model->progress = $progress;
        $this->model->save();

    }

    public function lesson_completed(LessonCompletedEvent $event){

        $this->user = $event->user;
        $model = ProgressUserDetail::where('user_id',$this->user->id)->first();
        $this->model = $model ?: new ProgressUserDetail();

        $this->model->user_id = $event->user->id;

        $progress = $this->model->progress;

        $c = [
            'course_id' => $event->course->id,
            'lesson_id' => $event->lesson->id,
            'time' => Carbon::now()->toDateTimeString()
        ];

        array_push($progress['lessons_completed'],$c);

        $this->model->progress = $progress;
        $this->model->save();

    }
}
