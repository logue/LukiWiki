<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Queue\Worker;
use Illuminate\View\View;

class JobController extends Controller
{
    private $worker;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        return view('dashboard/job', [
            'title'=> 'List of queues.',
            'jobs' => Job::paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): Response
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): Response
    {
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Job $job
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Job $job): Response
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Job $job
     *
     * @return \Illuminate\Http\Response
     */
    public function execute(Job $job): Response
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Job $job
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Job $job): Response
    {
    }

    public function work()
    {
        $connection = $this->getConnection();

        $this->queueWorker->runNextJob(
                $this->getConnection(),
                $this->getQueue($connection),
                new WorkerOptions()
            );

        return ['process complete'];
    }

    private function getConnection(): string
    {
        return config('queue.default');
    }

    private function getQueue(string $connection): string
    {
        return config("queue.connections.{$connection}.queue", 'default');
    }
}
