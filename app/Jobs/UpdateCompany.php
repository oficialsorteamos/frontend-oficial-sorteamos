<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class UpdateCompany implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $company)
    {
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $companyController = app(\App\Http\Controllers\Administration\CompanyController::class);
        //Cria um JOB para atualização dos dados da empresa
        $companyController->updateCompanyDetails($this->company);
    }
}
