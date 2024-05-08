<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {   
        //Rotina para fechamento de atendimentos em avaliação
        $schedule->call('App\Http\Controllers\Chat\ServiceController@closeServicesEvaluation')
                ->everyMinute();
        
        //Rotina que transfere os atendimentos automaticamente em caso de inatividade do contato
        $schedule->call('App\Http\Controllers\Chat\ServiceController@transferSelfService')
                ->everyMinute();
        
        //Rotina para envio de mensagens de campanha
        $schedule->call('App\Http\Controllers\Campaign\CampaignController@startCampaign')
                ->everyMinute();

        //Rotina para checar os status das campanhas na API
        $schedule->call('App\Http\Controllers\Campaign\CampaignController@checkStatusCampaignsApi')
                ->everyMinute();
        
        //Rotina para reiniciar o websocket todos os dias às 00:00 horas
        $schedule->call('App\Http\Controllers\Utils\UtilsController@restartWebsocket')
                ->dailyAt('00:00');
        
        //Rotina que verifica os status dos canais oficiais
        /*$schedule->call('App\Http\Controllers\Management\ChannelController@checkStatusChannel')
                ->everyFiveMinutes(); */
        
        //Lista e atualiza todos os templates disponíveis
        $schedule->call('App\Http\Controllers\Chat\TemplateController@listAndUpdateStatusTemplateFacebook')
                ->everyTenMinutes();

        //Rotina para fechar com mais de X dias abertos
        //$schedule->call('App\Http\Controllers\Chat\ServiceController@closeServicesOperator')
        //        ->everyMinute();
        
        //$schedule->call('App\Http\Controllers\Chat\ServiceController@closeSelfServices')
        //        ->everyMinute();

        //$schedule->call('App\Http\Controllers\Chat\ServiceController@closePendingServices')
        //        ->everyMinute();

        //$schedule->call('App\Http\Controllers\Chat\ServiceController@closeServicesByLastInteraction')
        //        ->everyMinute();

        //Rotina para criação das faturas
        $schedule->call('App\Http\Controllers\Financial\InvoiceController@generateInvoice')
                ->everySixHours();
        
        //Desconecta, e desabilita os canais com assinatura expirada
        $schedule->call('App\Http\Controllers\Management\ChannelController@disableExpiredSubscriptionsChannel')
                ->everyTwoHours();

        //Renova automaticamente a assinatura de canais
        $schedule->call('App\Http\Controllers\Management\ChannelController@automaticSubscriptionRenewal')
                ->everyTwoHours();

        //Assina automaticamente os canais que estão em modo trial
        $schedule->call('App\Http\Controllers\Management\ChannelController@subscribeTrialChannels')
                ->everyTwoHours();
        
        //Rotina para atualizar os dados das empresas todos dias às 00:05 horas
        $schedule->call('App\Http\Controllers\Administration\CompanyController@updateAllCompaniesData')
                ->dailyAt('00:05');
                //->everyMinute();

        //Gera as ordens de pagamento a serem feitas aos REVENDEDORES
        $schedule->call('App\Http\Controllers\Administration\PartnerController@generatePaymentOrders')
                ->dailyAt('06:00');
                //->everyMinute();

        //Gera as faturas a serem pagas pelos WHITE LABELS
        $schedule->call('App\Http\Controllers\Administration\PartnerController@generateInvoicePartner')
                ->dailyAt('05:00');
                //->everyMinute();

        //Verifica se existem gravações a serem salvas
        //$schedule->call('App\Http\Controllers\Api\Dialers\IpBoxController@getRecordsRoutine')
                //->dailyAt('05:00');
        //        ->everyMinute();

        //Cria as configurações de Distribuição Igualitária para canais
        //$schedule->call('App\Http\Controllers\Management\ChannelController@addFairDistributionConfChannel')
                //->dailyAt('05:00');
        //        ->everyMinute();

        //Cria as configurações de tempo de avaliação para canais
        $schedule->call('App\Http\Controllers\Management\ChannelController@addEvaluationTimeConfChannel')
                //->dailyAt('05:00');
                ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
