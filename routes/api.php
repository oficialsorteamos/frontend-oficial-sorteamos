<?php

use App\Http\Controllers\Administration\AdmNotificationController;
use App\Http\Controllers\Administration\CompanyController;
use App\Http\Controllers\Administration\InstanceController;
use App\Http\Controllers\Administration\PartnerController;
use App\Http\Controllers\Api\ApiEHostController;
use App\Http\Controllers\Api\ApiWaController;
use App\Http\Controllers\Api\ApiZapController;
use App\Http\Controllers\Api\Calendar\CalendlyController;
use App\Http\Controllers\Api\CloudApiWhatsAppController;
use App\Http\Controllers\Api\CommunicatorController;
use App\Http\Controllers\Api\Dialers\AzCallController;
use App\Http\Controllers\Api\Dialers\IpBoxController;
use App\Http\Controllers\Api\Dialog360Controller;
use App\Http\Controllers\Api\GupshupController;
use App\Http\Controllers\Api\Payment\AsaasController;
use App\Http\Controllers\Api\Sms\UnipixController;
use App\Http\Controllers\Api\Whatsapp\ZapligueController;
use App\Http\Controllers\Financial\CreditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Api\WhatsappController;
use App\Http\Controllers\Api\ZApiController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Campaign\MailingController;
use App\Http\Controllers\Campaign\OperatingFrequencyController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Chat\TemplateController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\Management\DepartmentController;
use App\Http\Controllers\Contact\PipelineController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Contact\SocialNetworkController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Financial\CardController;
use App\Http\Controllers\Financial\CostController;
use App\Http\Controllers\Financial\FeeController;
use App\Http\Controllers\Financial\InvoiceController;
use App\Http\Controllers\Financial\ParameterController;
use App\Http\Controllers\Integration\DialerController;
use App\Http\Controllers\Integration\VoipController;
use App\Http\Controllers\Management\BlacklistController;
use App\Http\Controllers\Management\CallController;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\ExtensionController;
use App\Http\Controllers\Management\GeneralMessageController;
use App\Http\Controllers\Management\TagController;
use App\Http\Controllers\System\AddressController;
use App\Http\Controllers\System\CountryController;
use App\Http\Controllers\System\GenderController;
use App\Http\Controllers\System\StateCountryController;
//use App\Http\Controllers\System\UserController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Report\OperatorEvaluationController;
use App\Http\Controllers\Report\ServiceReportController;
use App\Http\Controllers\Setting\CustomerController;
use App\Http\Controllers\Setting\PlanController;
use App\Http\Controllers\Setting\WhiteLabelController;
use App\Http\Controllers\System\PermissionController;
use App\Http\Controllers\System\ResourceController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Chatbot\ChatbotControl;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

});

Route::group(['middleware' => 'auth:sanctum'], function() {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('user', [AuthController::class, 'user']);

  Route::group(['prefix' => 'system'], function () {

    Route::group(['prefix' => 'gender'], function () {
      Route::get('fetch-genders', [GenderController::class, 'index']);
    });

    Route::group(['prefix' => 'state'], function () {
      Route::get('fetch-states', [StateCountryController::class, 'index']);
    });

    Route::group(['prefix' => 'country'], function () {
      Route::get('fetch-countries', [CountryController::class, 'index']);
    });

    Route::group(['prefix' => 'address'], function () {
      Route::get('fetch-address', [AddressController::class, 'fetchAddressesUser']);
      Route::post('add-address', [AddressController::class, 'store']);
      Route::post('update-address/{addressId}', [AddressController::class, 'update']);
      Route::delete('remove-address/{addressId}', [AddressController::class, 'destroy']);
    });

    Route::group(['prefix' => 'role'], function () {
      Route::get('fetch-roles', [RoleController::class, 'index']);
    });
  });

  ######################################################################################
  Route::group(['prefix' => 'dashboard'], function () {
    Route::get('fetch-statistics', [DashboardController::class, 'fetchStatistics']);
    Route::get('fetch-contacts-gender', [DashboardController::class, 'fetchContactsByGender']);
    Route::get('fetch-contacts-age-group', [DashboardController::class, 'fetchAgeGroupContacts']);
    Route::get('fetch-best-operators', [DashboardController::class, 'fetchBestOperators']);
    Route::get('fetch-services-last-months', [DashboardController::class, 'fetchServicesLastMonths']);
    Route::get('fetch-contacts-per-state', [DashboardController::class, 'fetchContactsState']);
    Route::get('fetch-services-by-status', [DashboardController::class, 'fetchCompareServiceStatus']);
  });

  ######################################################################################
  Route::group(['prefix' => 'chat'], function () {
    Route::get('chats-and-contacts', [ChatController::class, 'chatsAndContacts']);
    Route::get('active-chat', [ChatController::class, 'getChat']);
    Route::get('send-message-api', [WhatsappController::class, 'sendMessageApi']);
    Route::post('send-message', [ChatController::class, 'sendMessage']);
    Route::post('resend-message', [ChatController::class, 'resendMessage']);
    Route::get('quick-message', [ChatController::class, 'fetchQuickMessages']);
    Route::post('add-quick-message', [ChatController::class, 'addQuickMessage']);
    Route::post('update-quick-message/{quickMessageId}', [ChatController::class, 'updateQuickMessage']);
    Route::get('fetch-quick-messages-type-parameters/{statusId}', [ChatController::class, 'fetchQuickMessagesTypeParameters']);
    Route::delete('remove-quick-message/{quickMessageId}', [ChatController::class, 'removeQuickMessage']);
    Route::get('read-all-unseen-message/{chatId}', [ChatController::class, 'readAllUnseenMessage']);
    Route::get('fetch-messages-chat/', [ChatController::class, 'fetchMessagesChat']);

    Route::get('start-service/{chatId}/{channelId}', [ChatController::class, 'startService']);
    Route::get('close-service/', [ChatController::class, 'closeService']);
    Route::get('call-phone/', [ChatController::class, 'callPhone']);
    Route::get('services-contact/', [ServiceController::class, 'getServicesContact']);
    Route::get('services-user/', [ServiceController::class, 'getServicesUser']);
    Route::post('transfer-service', [ServiceController::class, 'transferService']);
    Route::get('situation-service-operator/{chatId}', [ServiceController::class, 'situationServiceOperator']);
    Route::post('update-fair-distribution', [ServiceController::class, 'updateFairDistribution']);
    Route::post('add-fair-distribution', [ServiceController::class, 'addFairDistribution']);
    Route::get('get-resources-fair-distribution/', [ServiceController::class, 'getResourcesFairDistribution']);
    Route::post('fecth-fair-distribution', [ServiceController::class, 'fetchFairDistribution']);
    Route::delete('remove-fair-distribution/{messageId}', [ServiceController::class, 'removeFairDistribution']);
    Route::get('get-fair-distributions', [ServiceController::class, 'getFairDistributions']);
    Route::get('get-fair-distribution-by-channel/{channelId}', [ServiceController::class, 'getFairDistributionsByChannel']);
    Route::get('get-fair-distribution-by-campaign/{campaignId}', [ServiceController::class, 'getFairDistributionsByCampaign']);
    
    Route::get('fetch-type-variables/{statusId}', [TemplateController::class, 'fetchTypeVariables']);
    Route::get('fetch-type-buttons/{statusId}', [TemplateController::class, 'fetchTypeButtons']);
    Route::get('fetch-type-headers/{statusId}', [TemplateController::class, 'fetchTypeHeaders']);
    Route::get('fetch-type-call-actions/{statusId}', [TemplateController::class, 'fetchTypeCallActions']);
    Route::get('fetch-template-categories', [TemplateController::class, 'fetchCategories']);
    Route::get('fetch-template-status', [TemplateController::class, 'fetchStatusTemplate']);
    Route::get('fetch-template-languages/{statusId}', [TemplateController::class, 'fetchLanguages']);
    Route::post('add-template-message', [TemplateController::class, 'store']);
    Route::get('fetch-templates/', [TemplateController::class, 'fetchTemplates']);
    Route::get('update-status-templates-facebook/', [TemplateController::class, 'listAndUpdateStatusTemplateFacebook']);
    Route::get('remove-template/', [TemplateController::class, 'removeTemplate']);
    Route::get('check-template-name-exist/{templateName}', [TemplateController::class, 'checkTemplateNameExist']);
    
    Route::get('fetch-campaign-templates/', [TemplateController::class, 'fetchCampaignTemplates']);
    Route::get('add-campaign-template/', [TemplateController::class, 'addCampaignTemplate']);
    Route::get('remove-campaign-template/', [TemplateController::class, 'removeCampaignTemplate']);

    Route::get('fetch-active-chats', [ChatController::class, 'fetchActiveChats']);
    Route::get('fetch-pending-chats', [ChatController::class, 'fetchPendingChats']);

    Route::get('get-base-url-storage', [ChatController::class, 'getBaseUrlStorage']);

    Route::get('fetch-chat-observations', [ChatController::class, 'fetchChatObservations']);
    Route::post('add-chat-observation', [ChatController::class, 'addChatObservation']);
    Route::delete('remove-chat-observation/{observationId}', [ChatController::class, 'removeChatObservation']);

    Route::get('fetch-type-format-messages', [ChatController::class, 'fetchTypeFormatMessages']);
  });

  ######################################################################################
  Route::group(['prefix' => 'calendar'], function () {
    Route::get('get-events', [CalendarController::class, 'index']);
    Route::post('add-event', [CalendarController::class, 'store']);
    Route::post('update-event/{eventId}', [CalendarController::class, 'update']);
    Route::delete('remove-event/{eventId}', [CalendarController::class, 'destroy']);

    Route::get('fetch-contacts', [CalendarController::class, 'fetchContacts']);
  });

  ######################################################################################
  Route::group(['prefix' => 'service'], function () {
    Route::post('add-service', [ServiceController::class, 'addService']);
    Route::get('fetch-services-progress/{params?}', [ServiceController::class, 'fetchServicesInProgress']);
    Route::post('transfer-service-progress', [ServiceController::class, 'transferServiceProgress']);
    Route::get('fetch-self-services-progress', [ServiceController::class, 'fetchSelfServiceChats']);
    Route::get('fetch-pending-services-progress', [ServiceController::class, 'fetchPendingServiceChats']);
    Route::get('fetch-active-services-progress', [ServiceController::class, 'fetchActiveServiceChats']);
    Route::get('fetch-status-services', [ServiceController::class, 'fetchStatusServices']);
  });

  ######################################################################################
  Route::group(['prefix' => 'chatbot'], function () {
    Route::get('fetch-chatbots', [ChatbotController::class, 'index']);
    Route::post('update-chatbot', [ChatbotController::class, 'update']);
    Route::post('add-chatbot', [ChatbotController::class, 'store']);
    Route::get('get-chatbot/{chatbotId}', [ChatbotController::class, 'show']);
    Route::delete('remove-chatbot/{chatbotId}', [ChatbotController::class, 'destroy']);
    Route::post('update-status-chatbot', [ChatbotController::class, 'updateStatusChatbot']);
    Route::get('fetch-channels-chatbot/{chatbotId}/{onlyOfficialChannel}', [ChatbotController::class, 'fetchChannelsChatbot']);
    Route::post('update-channel-chatbot', [ChatbotController::class, 'updateChannelChatbot']);
    Route::get('fetch-type-chatbots', [ChatbotController::class, 'fetchTypeChatbots']);

    Route::post('add-bloc', [ChatbotController::class, 'addBloc']);
    Route::get('fetch-blocs', [ChatbotController::class, 'fetchBlocs']);
    Route::post('update-bloc/{blocId}', [ChatbotController::class, 'updateBloc']);
    Route::delete('remove-bloc/{blocId}', [ChatbotController::class, 'removeBloc']);
    Route::get('fetch-type-actions', [ChatbotController::class, 'fetchTypeActions']);
    Route::get('fetch-destination-blocs/{chatbotId}', [ChatbotController::class, 'fetchDestinationBlocs']);

    Route::post('add-action', [ChatbotController::class, 'addAction']);
    Route::post('update-action/{actionId}', [ChatbotController::class, 'updateAction']);
    Route::delete('remove-action/{actionId}', [ChatbotController::class, 'removeAction']);
  });

  ######################################################################################
  Route::group(['prefix' => 'integration'], function () {
    Route::group(['prefix' => 'voip'], function () {
      Route::get('get-voips', [VoipController::class, 'index']);
      Route::get('fetch-voips', [VoipController::class, 'fetchVoips']);
      Route::post('update-authentication-voip', [VoipController::class, 'updateAuthenticationVoip']);
      Route::post('update-status-voip', [VoipController::class, 'updateStatusVoip']);
    });

    Route::group(['prefix' => 'dialer'], function () {
      Route::get('fetch-dialers', [DialerController::class, 'fetchDialers']);
      Route::get('fetch-fowarding-settings', [DialerController::class, 'fetchFowardingSettings']);
      Route::get('fetch-channels-fowarding/{dialerId}', [DialerController::class, 'fetchChannelsFowarding']);
      Route::post('add-fowarding-setting', [DialerController::class, 'addFowardingSetting']);
      Route::post('update-fowarding-setting', [DialerController::class, 'updateFowardingSetting']);
      Route::delete('remove-fowarding-setting/{fowardingSettingId}', [DialerController::class, 'removeFowardingSetting']);
    });
  });
  ######################################################################################
  Route::group(['prefix' => 'management'], function () {
    
    Route::group(['prefix' => 'department'], function () {
      Route::get('fetch-departments', [DepartmentController::class, 'index']);
      Route::get('fetch-departments-filter/{params?}', [DepartmentController::class, 'fetchDepartments']);
      Route::post('add-department/', [DepartmentController::class, 'store']);
      Route::post('update-department/', [DepartmentController::class, 'update']);
      Route::delete('remove-department/{departmentId}', [DepartmentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'channel'], function () {
      Route::get('fetch-channels', [ChannelController::class, 'index']);
      Route::post('add-channel/', [ChannelController::class, 'store']);
      Route::post('update-channel/', [ChannelController::class, 'update']);
      Route::post('update-parameters-channel/', [ChannelController::class, 'updateParametersChannel']);
      Route::get('update-status-channel/{params?}', [ChannelController::class, 'updateStatusChannel']);
      Route::get('check-phone-exist/{phoneNumber}', [ChannelController::class, 'checkPhoneExist']);
      Route::get('fetch-channels-official-api/{officialApi}', [ChannelController::class, 'fetchChannelsByOfficialApi']);
      Route::get('fetch-channels-by-status/{statusId}', [ChannelController::class, 'fetchChannelsByStatus']);
      Route::get('fetch-apis-communication/{apiOfficial}', [ChannelController::class, 'fetchApisCommunicationByType']);
      Route::post('add-payment/', [ChannelController::class, 'addPayment']);
      Route::post('fetch-channel-payments/', [ChannelController::class, 'fetchChannelPayments']);
      Route::post('update-subscription-renewal/', [ChannelController::class, 'updateSubscriptionRenewal']);
      Route::post('hide-notification/', [ChannelController::class, 'hideNotification']);
    });

    Route::group(['prefix' => 'user'], function () {
      Route::get('fetch-users', [UserController::class, 'index']);
      Route::get('get-operators', [UserController::class, 'getOperators']);
      Route::get('fetch-users-filter/{params?}', [UserController::class, 'fetchUsers']);
      Route::get('fetch-user/{id}', [UserController::class, 'show']);
      Route::post('add-user/', [UserController::class, 'store']);
      Route::delete('remove-user/{userId}', [UserController::class, 'destroy']);
      Route::post('update-user/', [UserController::class, 'update']);
      Route::post('update-access-detail/', [UserController::class, 'updateAccessDetailAccount']);
      Route::post('update-notification/', [UserController::class, 'updateUserNotification']);
      Route::post('update-user-address/', [UserController::class, 'updateUserAddress']);
      Route::delete('remove-user/{userId}', [UserController::class, 'destroy']);
      Route::get('get-user', [UserController::class, 'getUsers']);
      Route::get('get-user-without-logged', [UserController::class, 'getUsersWithoutLogged']);
      Route::get('get-user-department/{departmentId}', [UserController::class, 'getUsersDepartment']);
      Route::get('get-user-department-transfer/{departmentId}', [UserController::class, 'getUsersDepartmentTransfer']);
      Route::get('profile-user', [UserController::class, 'getProfileUser']);
      Route::get('update-user-situation/{params?}', [UserController::class, 'updateUserSituation']);
      Route::post('upload-photo', [UserController::class, 'uploadPhoto']);
      Route::post('send-alert-notification/', [UserController::class, 'sendAlertNotification']);
      Route::get('fetch-user-notification/{userId}', [UserController::class, 'fetchUserNotification']);
      Route::get('mark-notification-as-read/{userId}', [UserController::class, 'markNotificationAsRead']);
      Route::get('get-users-by-role/{roleId}', [UserController::class, 'getUsersByRole']);
      Route::get('get-system-users', [UserController::class, 'getSystemUsers']);
      Route::get('get-users-by-status/{statusId}', [UserController::class, 'getUsersByStatus']);
    });

    Route::group(['prefix' => 'tag'], function () {
      Route::get('fetch-tags-type/{typeId}', [TagController::class, 'fetchTagsType']);
      Route::get('fetch-tags-filter/{params?}', [TagController::class, 'fetchTags']);
      Route::get('fetch-types-tag', [TagController::class, 'fetchTypesTag']);
      Route::post('add-tag/', [TagController::class, 'store']);
      Route::post('update-tag/', [TagController::class, 'update']);
      Route::delete('remove-tag/{tagId}', [TagController::class, 'destroy']);
    });

    Route::group(['prefix' => 'blacklist'], function () {
      Route::get('fetch-blacklist/{params?}', [BlacklistController::class, 'fetchBlacklist']);
      Route::post('add-contact-blacklist/', [BlacklistController::class, 'store']);
      Route::delete('remove-contact-blacklist/{contactId}', [BlacklistController::class, 'destroy']);
      Route::get('fetch-contacts/{params?}', [BlacklistController::class, 'fetchContacts']);
    });

    Route::group(['prefix' => 'call'], function () {
      Route::get('fetch-calls/{params?}', [CallController::class, 'index']);
    });

    Route::group(['prefix' => 'extension'], function () {
      Route::get('fetch-extensions/{params?}', [ExtensionController::class, 'index']);
      Route::get('get-extensions', [ExtensionController::class, 'getExtensions']);
      Route::post('add-extension/', [ExtensionController::class, 'store']);
      Route::post('update-extension/', [ExtensionController::class, 'update']);
      Route::delete('remove-extension/{extensionId}', [ExtensionController::class, 'destroy']);
    });

    Route::group(['prefix' => 'general-message'], function () {
      Route::get('fetch-general-messages', [GeneralMessageController::class, 'index']);
      Route::post('update-general-message/', [GeneralMessageController::class, 'update']);
    });

    Route::group(['prefix' => 'permission-control'], function () {
      Route::group(['prefix' => 'role'], function () {
        Route::get('fetch-roles', [RoleController::class, 'fetchRoles']);
      });

      Route::group(['prefix' => 'resource'], function () {
        Route::get('get-resources/{roleId}', [ResourceController::class, 'getResources']);
      });

      Route::group(['prefix' => 'permission'], function () {
        Route::post('update-permission-role/', [PermissionController::class, 'updatePermissionRole']);
      });
    });
  });

  ######################################################################################
  Route::group(['prefix' => 'report'], function () {
    Route::group(['prefix' => 'service'], function () {
      Route::get('fetch-services/{params?}', [ServiceReportController::class, 'fetchServices']);
      Route::post('download-service-report', [ServiceReportController::class, 'downloadServiceReport']);
    });
    Route::group(['prefix' => 'operator-evaluation'], function () {
      Route::get('fetch-operator-evaluation/{params?}', [OperatorEvaluationController::class, 'fetchOperatorEvaluation']);
    });
  });

  ######################################################################################
  Route::group(['prefix' => 'contact'], function () {
    Route::post('update-tag', [ContactController::class, 'updateTag']);

    Route::get('fetch-contacts/{params?}', [ContactController::class, 'index']);
    Route::get('fetch-contact/{id}', [ContactController::class, 'show']);
    Route::get('block-contact/{contactId}', [ContactController::class, 'blockContact']);
    Route::get('unlock-contact/{contactId}', [ContactController::class, 'unlockContact']);
    Route::post('add-contact', [ContactController::class, 'store']);
    Route::post('update-contact/{contactId}', [ContactController::class, 'update']);
    Route::post('upload-photo', [ContactController::class, 'uploadPhoto']);
    Route::post('add-quick-contact', [ContactController::class, 'addQuickContact']);

    Route::get('fetch-type-social-networks', [SocialNetworkController::class, 'fetchTypeSocialNetworks']);
    Route::post('add-social-network', [SocialNetworkController::class, 'store']);
    Route::get('fetch-social-networks', [SocialNetworkController::class, 'fetchSocialNetworksContact']);
    Route::post('update-social-network/{socialId}', [SocialNetworkController::class, 'update']);
    Route::delete('remove-social-network/{socialId}', [SocialNetworkController::class, 'destroy']);
    Route::get('fetch-contacts-chat/{params?}', [ContactController::class, 'fetchContactsChat']);
    
    Route::get('add-contact-blacklist-campaign/', [ContactController::class, 'addContactBlacklistCampaign']);

    Route::post('update-contact-general/', [ContactController::class, 'updateContactGeneral']);
  });

  ######################################################################################
  Route::group(['prefix' => 'utils'], function () {
    Route::get('get-address-api/{cep}', [UtilsController::class, 'getAddressApi']);
  });

  ######################################################################################
  Route::group(['prefix' => 'campaign'], function () {
    Route::post('add-campaign', [CampaignController::class, 'store']);
    Route::post('update-campaign', [CampaignController::class, 'update']);
    Route::delete('remove-campaign/{campaignId}', [CampaignController::class, 'destroy']);
    Route::get('fetch-campaigns', [CampaignController::class, 'index']);
    Route::post('update-status-campaign', [CampaignController::class, 'updateStatusCampaign']);
    Route::post('fetch-channels-chatbots', [CampaignController::class, 'fetchChannelsChatbots']);
    Route::post('fetch-channels-chatbots-campaign', [CampaignController::class, 'fetchChannelsChatbotsCampaign']);
    Route::post('add-channel-chatbot-campaign', [CampaignController::class, 'addChannelChatbotCampaign']);
    Route::delete('remove-channel-chatbot-campaign/{channelChatbotId}', [CampaignController::class, 'removeChannelChatbotCampaign']);
    Route::get('fetch-chatbots-campaign/{channelId}', [CampaignController::class, 'fetchChatbotsCampaign']);
    Route::get('fetch-type-campaigns', [CampaignController::class, 'fetchTypeCampaigns']);

    Route::get('fetch-messages/{campaignId}', [CampaignController::class, 'fetchMessages']);
    Route::post('add-message', [CampaignController::class, 'addMessage']);
    Route::delete('remove-message/{messageId}', [CampaignController::class, 'removeMessage']);
    Route::post('update-message', [CampaignController::class, 'updateMessage']);

    Route::get('fetch-mailing/{params?}', [MailingController::class, 'fetchMailing']);
    Route::post('add-mailing', [CampaignController::class, 'addMailing']);
    Route::post('download-mailing-model', [MailingController::class, 'downloadMailingModel']);
    Route::post('remove-contact-mailing', [CampaignController::class, 'removeContactMailing']);
    Route::post('download-mailing', [MailingController::class, 'downloadMailing']);
    Route::get('fetch-status-mailng', [MailingController::class, 'fetchStatusMailing']);

    Route::post('update-channel-campaign', [CampaignController::class, 'updateChannelCampaign']);

    Route::get('fetch-operating-frequency', [OperatingFrequencyController::class, 'index']);
    Route::post('update-operating-frequency', [CampaignController::class, 'updateOperatingFrequency']);

    Route::get('fetch-number-shots-frequency', [OperatingFrequencyController::class, 'fetchNumberShotsFrequency']);

    Route::post('update-operating-hours', [CampaignController::class, 'updateOperatingHours']);

    Route::post('update-forwarding', [CampaignController::class, 'updateForwarding']);
  });

  ######################################################################################

  Route::group(['prefix' => 'notification'], function () {
    Route::get('fetch-notifications/', [NotificationController::class, 'fetchNotifications']);
  });

  ######################################################################################
  Route::group(['prefix' => 'communication'], function () {
    Route::group(['prefix' => 'communicator'], function () {
      Route::post('start-session', [CommunicatorController::class, 'startSession']);
      Route::post('close-session', [CommunicatorController::class, 'closeSession']);
    });
  });

  ######################################################################################
  Route::group(['prefix' => 'financial'], function () {
    Route::get('fetch-invoices/', [InvoiceController::class, 'fetchInvoices']);
    Route::get('get-pix-qrcode/{chargeIdApi}', [InvoiceController::class, 'getPixQrcode']);
    Route::get('fetch-status-payments', [InvoiceController::class, 'fetchStatusPayments']);

    Route::get('fetch-payment-methods', [CreditController::class, 'fetchPaymentMethods']);
    Route::get('fetch-cards', [CardController::class, 'index']);
    Route::post('add-card', [CardController::class, 'store']);
    Route::post('update-card', [CardController::class, 'updateHolderInfo']);
    Route::delete('remove-card/{cardId}', [CardController::class, 'destroy']);
    Route::get('fetch-cards-by-type/{typeCardId}', [CreditController::class, 'fetchCardsByType']);
    
    Route::post('add-credit', [CreditController::class, 'store']);
    Route::get('fetch-credits/', [CreditController::class, 'index']);
    Route::get('get-total-balance/', [CreditController::class, 'getTotalBalance']);

    Route::get('fetch-costs', [CostController::class, 'index']);

    Route::get('fetch-parameters-charge', [ParameterController::class, 'fetchParametersCharge']);
    Route::post('update-parameters-charge', [ParameterController::class, 'updateParametersCharge']);
    Route::get('fetch-parameter-by-type/{parameterTypeId}', [ParameterController::class, 'fetchParameterByType']);

    Route::get('fetch-parameters-general', [ParameterController::class, 'fetchParametersGeneral']);
    Route::post('update-parameters-general', [ParameterController::class, 'updateParametersGeneral']);

    Route::get('fetch-fees/', [FeeController::class, 'index']);
    Route::post('update-fee', [FeeController::class, 'update']);
    Route::get('fetch-fee-by-type/{feeType}', [FeeController::class, 'fetchFeeByType']);

    Route::get('get-overdue-bills', [InvoiceController::class, 'getCountOverdueBills']);
  });
  ########################################################################################################################
  Route::group(['prefix' => 'settings'], function () {
    Route::group(['prefix' => 'company'], function () {
      Route::get('fetch-company', [CustomerController::class, 'index']);
      Route::post('update-company-general/', [CustomerController::class, 'update']);
      Route::get('get-company/', [CustomerController::class, 'getCustomer']);
      Route::post('update-customization/', [CustomerController::class, 'updateCustomization']);
    });
    Route::group(['prefix' => 'plan'], function () {
      Route::get('fetch-plan', [PlanController::class, 'index']);
      Route::post('update-plan/', [PlanController::class, 'update']);
    });
    Route::group(['prefix' => 'white-label'], function () {
      Route::get('fetch-white-label', [WhiteLabelController::class, 'index']);
      Route::post('update-white-label/', [WhiteLabelController::class, 'update']);
    });
  });
  #########################################################################################################################
  Route::group(['prefix' => 'administration'], function () {
    Route::group(['prefix' => 'partner'], function () {
      Route::get('fetch-partners', [PartnerController::class, 'index']);
      Route::get('get-partners-by-status/{statusId}', [PartnerController::class, 'getPartnersByStatus']);
      Route::get('get-partners', [PartnerController::class, 'getPartners']);
      Route::get('fetch-type-partners', [PartnerController::class, 'fetchTypePartners']);
      Route::post('add-partner', [PartnerController::class, 'store']);
      Route::post('update-partner', [PartnerController::class, 'update']);
      Route::post('update-commission', [PartnerController::class, 'updateCommission']);
      Route::post('update-partner-fees', [PartnerController::class, 'updatePartnerFees']);

      Route::group(['prefix' => 'payment-order'], function () {
        Route::get('fetch-payment-orders', [PartnerController::class, 'fetchPaymentOrders']);
        Route::post('update-payment-order', [PartnerController::class, 'updatePaymentOrder']);
        Route::get('fetch-payment-order-status', [PartnerController::class, 'fetchPaymentOrderStatus']);
        Route::post('upload-payment-receipt', [PartnerController::class, 'uploadPaymentReceipt']);
      });

      Route::group(['prefix' => 'invoice'], function () {
        Route::post('fetch-invoices', [PartnerController::class, 'fetchInvoices']);
      });
    });
    Route::group(['prefix' => 'company'], function () {
      Route::get('fetch-companies', [CompanyController::class, 'index']);
      Route::post('add-company', [CompanyController::class, 'store']);
      Route::post('update-company', [CompanyController::class, 'update']);
      Route::get('get-companies-by-status/{statusId}', [CompanyController::class, 'getCompaniesByStatus']);
      Route::post('update-company-details', [CompanyController::class, 'updateCompanyDetails']);
      Route::post('add-contract', [CompanyController::class, 'addContract']);
      Route::post('update-contract', [CompanyController::class, 'updateContract']);
      Route::post('fetch-contracts', [CompanyController::class, 'fetchContracts']);
      Route::post('download-contract', [CompanyController::class, 'downloadContract']);
      Route::delete('remove-contract/{contractId}', [CompanyController::class, 'removeContract']);
      Route::get('fetch-company-status/{statusId}', [CompanyController::class, 'fetchCompanyStatus']);
      Route::get('update-company-status/{companyId}/{statusId}', [CompanyController::class, 'updateCompanyStatus']);
      Route::post('update-company-plan', [CompanyController::class, 'updateCompanyPlan']);
      Route::post('update-company-fees', [CompanyController::class, 'updateCompanyFees']);
      Route::post('update-company-charges', [CompanyController::class, 'updateCompanyCharges']);
    });
    Route::group(['prefix' => 'notification'], function () {
      Route::get('fetch-notifications', [AdmNotificationController::class, 'index']);
      Route::post('send-notification', [AdmNotificationController::class, 'sendNotification']);
      Route::get('fetch-companies-by-notification', [AdmNotificationController::class, 'fetchCompaniesByNotification']);
    });
    Route::group(['prefix' => 'instance'], function () {
      Route::get('fetch-instances', [InstanceController::class, 'index']);
      Route::get('update-instances', [InstanceController::class, 'updateInstances']);
      Route::post('disconnect-instance', [InstanceController::class, 'disconnectInstance']);
      Route::post('remove-instance', [InstanceController::class, 'removeInstance']);
      Route::get('get-instance-status', [InstanceController::class, 'getInstanceStatus']);
      Route::get('get-instance-connection-status', [InstanceController::class, 'getInstanceConnectionStatus']);
      Route::get('get-apis-by-official/{official}', [CommunicatorController::class, 'getApisByOfficial']);
    });
  });

  //Route::post('/webhook-360-dialog/{channelId}', [ChatController::class, 'webhook360Dialog'])->name('chat.webhook360Dialog');
});



Route::get('/api-cloud/', [CloudApiWhatsAppController::class, 'apiCloudWebhook'])->name('cloudApi.apiCloudWebhook');
Route::post('/api-cloud/', [CloudApiWhatsAppController::class, 'apiCloudWebhook'])->name('cloudApi.apiCloudWebhook');
Route::post('/webhook-gupshup/', [GupshupController::class, 'webhookGupshup'])->name('gupshup.webhookGupshup');
Route::post('/webhook-360-dialog/{channelId}', [Dialog360Controller::class, 'webhook360Dialog'])->name('dialog360.webhook360Dialog');
Route::post('/webhook-z-api/{channelId}', [ZApiController::class, 'webhookZApi'])->name('zApi.webhookNodeApi');
Route::post('/receive-message', [ChatController::class, 'receiveMessage'])->name('chat.receiveMessage');
Route::post('/webhook-apizap/{channelId}', [ApiZapController::class, 'webhookApiZap'])->name('apiZap.webhookApiZap');
Route::post('/webhook-api-wa/{channelId}', [ApiWaController::class, 'webhookApiWa'])->name('apiWa.webhookApiWa');
Route::post('/webhook-api-ehost/{channelId}', [ApiEHostController::class, 'webhookApiEHost'])->name('apiEHost.webhookApiEHost');


#################################### Webhook Payments #################################
Route::post('/webhook-asaas-charges', [AsaasController::class, 'webhookCharges'])->name('asaas.webhookCharges');
Route::post('/forward-data-webhook', [AsaasController::class, 'forwardDataWebhook'])->name('asaas.forwardDataWebhook');


#################################### Dialers ##########################################
Route::get('/webhook-ipbox', [IpBoxController::class, 'webhookIpBox'])->name('ipbox.webhookIpBox');
Route::post('/webhook-azcall', [AzCallController::class, 'webhookAzCall'])->name('azCall.webhookAzCall');
Route::get('/webhook-azcall', [AzCallController::class, 'webhookAzCall'])->name('azCall.webhookAzCall');

#################################### SMS ##############################################
Route::post('/webhook-unipix', [UnipixController::class, 'webhookUnipix'])->name('unipix.webhookUnipix');

#################################### WhatsApp Call ####################################
Route::post('/zapligue/webhook-call-whatsapp/', [ZapligueController::class, 'webhookCallWhatsappZapligue'])->name('zapligue.webhookCallWhatsappZapligue');

#################################### WhatsApp Call ####################################
Route::post('/webhook-api-calendly', [CalendlyController::class, 'webhookCalendly'])->name('apiCalendly.webhookCalendly');



Route::post('/generate-invoice', [InvoiceController::class, 'generateInvoice']);


#################################### API CHATX ####################################
Route::group(['middleware' => 'api_token'], function() {
  Route::group(['prefix' => 'administration'], function () {
    Route::group(['prefix' => 'notification'], function () {
      Route::post('/webhook-notification', [NotificationController::class, 'webhookNotification'])->name('notification.webhookNotification');
    });
    Route::group(['prefix' => 'company'], function () {
      Route::post('/get-company-by-document-id', [CompanyController::class, 'getCompanyByDocumentId'])->name('company.getCompanyByDocumentId');
      Route::post('/update-company-api', [CompanyController::class, 'update'])->name('company.update');
      Route::post('/add-company-api', [CompanyController::class, 'store'])->name('company.store');
    });
    Route::group(['prefix' => 'partner'], function () {
      Route::post('/get-partner-by-document-id', [PartnerController::class, 'getPartnerByDocumentId'])->name('partner.getPartnerByDocumentId');
    });
  });
  Route::group(['prefix' => 'setting'], function () {
    Route::group(['prefix' => 'company'], function () {
      Route::post('/get-company-details', [CustomerController::class, 'getCompanyDetails'])->name('company.getCompanyDetails');
      Route::post('/update-company-status', [CustomerController::class, 'updateCompanyStatus'])->name('company.updateCompanyStatus');
      Route::post('/update-company-api', [CustomerController::class, 'update'])->name('company.update');
    });
    Route::group(['prefix' => 'plan'], function () {
      Route::post('/update-company-plan', [PlanController::class, 'updateCompanyPlan'])->name('company.updateCompanyPlan');
    });
  });
  Route::group(['prefix' => 'financial'], function () {
    Route::group(['prefix' => 'fee'], function () {
      Route::post('/update-company-fees', [FeeController::class, 'updateCompanyFees'])->name('fee.updateCompanyFees');
    });
    Route::group(['prefix' => 'parameter'], function () {
      Route::post('/update-company-charges', [ParameterController::class, 'updateCompanyCharges'])->name('paramenter.updateCompanyCharges');
    });
    Route::group(['prefix' => 'invoice'], function () {
      Route::post('/get-invoices-by-status', [InvoiceController::class, 'getInvoicesByStatus'])->name('company.getInvoicesByStatus');
    });
  });
});
