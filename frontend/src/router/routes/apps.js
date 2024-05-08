export default [
  {
    path: '/apps/calendar',
    name: 'apps-calendar',
    component: () => import('@/views/apps/calendar/Calendar.vue'),
    meta: {
      resource: 'calendar',
      action: 'menu_calendar',
    }
  },

  // *===============================================---*
  // *--------- EMAIL & IT'S FILTERS N LABELS -------------------------------*
  // *===============================================---*
  {
    path: '/apps/email',
    name: 'apps-email',
    component: () => import('@/views/apps/email/Email.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'email-application',
    },
  },
  {
    path: '/apps/email/:folder',
    name: 'apps-email-folder',
    component: () => import('@/views/apps/email/Email.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'email-application',
      navActiveLink: 'apps-email',
    },
    beforeEnter(to, _, next) {
      if (['sent', 'draft', 'starred', 'spam', 'trash'].includes(to.params.folder)) next()
      else next({ name: 'error-404' })
    },
  },
  {
    path: '/apps/email/label/:label',
    name: 'apps-email-label',
    component: () => import('@/views/apps/email/Email.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'email-application',
      navActiveLink: 'apps-email',
    },
    beforeEnter(to, _, next) {
      if (['personal', 'company', 'important', 'private'].includes(to.params.label)) next()
      else next({ name: 'error-404' })
    },
  },

  // *===============================================---*
  // *--------- DASHBOARD ---------------------------------------*
  // *===============================================---*

  {
    path: '/apps/dashboard',
    name: 'apps-dashboard',
    component: () => import('@/views/apps/dashboard/Dashboard.vue'),
    meta: {
      //Permission
      resource: 'dashboard',
      action: 'menu_dashboard',
    },
  },

  // *===============================================---*
  // *--------- CHATBOT & IT'S FILTERS N TAGS ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/chatbot',
    name: 'apps-chatbot',
    component: () => import('@/views/apps/chatbot/Chatbot.vue'),
    meta: {
      //contentRenderer: 'sidebar-left',
      //contentClass: 'chatbot-application',
      //Permission
      resource: 'chatbot',
      action: 'menu_chatbot',
    },
  },
  {
    path: '/apps/chatbot/view-structure/:id',
    name: 'apps-chatbot-view-structure',
    component: () => import('@/views/apps/chatbot/chatbot-card-handler/chatbot-blocs-structure-handler/ChatbotBlocsStructureHandler.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'chatbot-application',
      //Permission
      resource: 'chatbot',
      action: 'menu_chatbot',
    }
  },
  {
    path: '/apps/chatbot/:filter',
    name: 'apps-chatbot-filter',
    component: () => import('@/views/apps/chatbot/Chatbot.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'chatbot-application',
      navActiveLink: 'apps-chatbot',
    },
    beforeEnter(to, _, next) {
      if (['important', 'completed', 'deleted'].includes(to.params.filter)) next()
      else next({ name: 'error-404' })
    },
  },
  {
    path: '/apps/chatbot/tag/:tag',
    name: 'apps-chatbot-tag',
    component: () => import('@/views/apps/chatbot/Chatbot.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'chatbot-application',
      navActiveLink: 'apps-chatbot',
    },
    beforeEnter(to, _, next) {
      if (['team', 'low', 'medium', 'high', 'update'].includes(to.params.tag)) next()
      else next({ name: 'error-404' })
    },
  },

  // *===============================================---*
  // *--------- CHAT  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/chat',
    name: 'apps-chat',
    component: () => import('@/views/apps/chat/Chat.vue'),
    meta: {
      contentRenderer: 'sidebar-left',
      contentClass: 'chat-application',
      //Permission
      resource: 'chat',
      action: 'menu_chat',
    },
  },

  // *===============================================---*
  // *--------- SERVICES ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/services',
    name: 'apps-services',
    component: () => import('@/views/apps/service/Service.vue'),
    meta: {
      //Permission
      resource: 'service',
      action: 'menu_service',
    }
  },

  // *===============================================---*
  // *--------- ECOMMERCE  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/e-commerce/shop',
    name: 'apps-e-commerce-shop',
    component: () => import('@/views/apps/e-commerce/e-commerce-shop/ECommerceShop.vue'),
    meta: {
      contentRenderer: 'sidebar-left-detached',
      contentClass: 'ecommerce-application',
      pageTitle: 'Shop',
      breadcrumb: [
        {
          text: 'ECommerce',
        },
        {
          text: 'Shop',
          active: true,
        },
      ],
    },
  },
  {
    path: '/apps/e-commerce/wishlist',
    name: 'apps-e-commerce-wishlist',
    component: () => import('@/views/apps/e-commerce/e-commerce-wishlist/ECommerceWishlist.vue'),
    meta: {
      pageTitle: 'Wishlist',
      contentClass: 'ecommerce-application',
      breadcrumb: [
        {
          text: 'ECommerce',
        },
        {
          text: 'Wishlist',
          active: true,
        },
      ],
    },
  },
  {
    path: '/apps/e-commerce/checkout',
    name: 'apps-e-commerce-checkout',
    component: () => import('@/views/apps/e-commerce/e-commerce-checkout/ECommerceCheckout.vue'),
    meta: {
      pageTitle: 'Checkout',
      contentClass: 'ecommerce-application',
      breadcrumb: [
        {
          text: 'ECommerce',
        },
        {
          text: 'Checkout',
          active: true,
        },
      ],
    },
  },
  {
    path: '/apps/e-commerce/:slug',
    name: 'apps-e-commerce-product-details',
    component: () => import('@/views/apps/e-commerce/e-commerce-product-details/ECommerceProductDetails.vue'),
    meta: {
      pageTitle: 'Product Details',
      contentClass: 'ecommerce-application',
      breadcrumb: [
        {
          text: 'ECommerce',
        },
        {
          text: 'Shop',
          active: true,
        },
        {
          text: 'Product Details',
          active: true,
        },
      ],
    },
  },

  // *===============================================---*
  // *--------- CONTACT ---- ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/contacts/list',
    name: 'apps-contacts-list',
    component: () => import('@/views/apps/contact/contacts-list/ContactsList.vue'),
    meta: {
      //Permission
      resource: 'contact',
      action: 'menu_contact',
    }
  },
  {
    path: '/apps/contacts/view/:id',
    name: 'apps-contacts-view',
    component: () => import('@/views/apps/contact/contacts-view/ContactsView.vue'),
    meta: {
      //Permission
      resource: 'contact',
      action: 'menu_contact',
    }
  },
  {
    path: '/apps/contacts/edit/:id',
    name: 'apps-contacts-edit',
    component: () => import('@/views/apps/contact/contacts-edit/ContactsEdit.vue'),
    meta: {
      //Permission
      resource: 'contact',
      action: 'menu_contact',
    }
  },

  // *===============================================---*
  // *--------- INTEGRATIONS  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/integrations/voip',
    name: 'apps-integrations-voip',
    component: () => import('@/views/apps/integrations/voip/Voip.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/integrations/dialers',
    name: 'apps-integrations-dialers',
    component: () => import('@/views/apps/integrations/dialer/Dialer.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },

  // *===============================================---*
  // *--------- MANAGEMENT  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/management/channels',
    name: 'apps-management-channels',
    component: () => import('@/views/apps/management/channels/Channel.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/departments',
    name: 'apps-management-departments',
    component: () => import('@/views/apps/management/departments/Department.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/tags',
    name: 'apps-management-tags',
    component: () => import('@/views/apps/management/tags/Tag.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/templates',
    name: 'apps-management-templates',
    component: () => import('@/views/apps/management/templates/Template.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/users',
    name: 'apps-management-users',
    component: () => import('@/views/apps/management/users/User.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/users/view/:id',
    name: 'apps-management-users-view',
    component: () => import('@/views/apps/management/users/users-view/UsersView.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/general-messages',
    name: 'apps-management-general-messages',
    component: () => import('@/views/apps/management/general-messages/GeneralMessage.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/blacklist',
    name: 'apps-management-blacklist',
    component: () => import('@/views/apps/management/blacklist/Blacklist.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/calls',
    name: 'apps-management-calls',
    component: () => import('@/views/apps/management/calls/Call.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/extensions',
    name: 'apps-management-extensions',
    component: () => import('@/views/apps/management/extensions/Extension.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },
  {
    path: '/apps/management/roles',
    name: 'apps-management-roles',
    component: () => import('@/views/apps/management/permissions-control/roles/Role.vue'),
    meta: {
      //Permission
      resource: 'management',
      action: 'menu_management',
    }
  },

// *===============================================---*
  // *--------- REPORTS  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/report/services',
    name: 'apps-reports-services',
    component: () => import('@/views/apps/report/services/Service.vue'),
    meta: {
      //Permission
      resource: 'reports',
      action: 'menu_reports_services',
    }
  },
  {
    path: '/apps/report/operator-evaluation',
    name: 'apps-reports-operator-evaluation',
    component: () => import('@/views/apps/report/operator-evaluation/OperatorEvaluation.vue'),
    meta: {
      //Permission
      resource: 'reports',
      action: 'menu_reports_services',
    }
  },

  // *===============================================---*
  // *--------- CAMPAIGN ---------------------------------------*
  // *===============================================---*

  {
    path: '/apps/campaign',
    name: 'apps-campaign',
    component: () => import('@/views/apps/campaign/Campaign.vue'),
    meta: {
      //Permission
      resource: 'campaign',
      action: 'menu_campaign',
    },
  },
  {
    path: '/apps/campaign/view-mailing/:id',
    name: 'apps-campaign-view-mailing',
    component: () => import('@/views/apps/campaign/campaign-card-handler/campaign-view-mailing/ContactsMailing.vue'),
    meta: {
      //Permission
      resource: 'campaign',
      action: 'menu_campaign',
    }
  },


  // *===============================================---*
  // *--------- NOTIFICATIONS -------------------------------*
  // *===============================================---*
  {
    path: '/apps/notifications',
    name: 'apps-notifications',
    component: () => import('@/views/apps/notifications/Notification.vue'),
    meta: {
      //Permission
      resource: 'notification',
      action: 'menu_notification',
    }
  },


  // *===============================================---*
  // *--------- FINANCIAL  -----------------------------------*
  // *===============================================---*
  {
    path: '/apps/financial/invoices',
    name: 'apps-financial-invoices',
    component: () => import('@/views/apps/financial/invoices/Invoice.vue'),
    meta: {
      //Permission
      resource: 'financial',
      action: 'menu_invoices',
    }
  },
  {
    path: '/apps/financial/credits',
    name: 'apps-financial-credits',
    component: () => import('@/views/apps/financial/credits/Credit.vue'),
    meta: {
      //Permission
      resource: 'financial',
      action: 'menu_credits',
    }
  },
  {
    path: '/apps/financial/costs',
    name: 'apps-financial-costs',
    component: () => import('@/views/apps/financial/costs/Cost.vue'),
    meta: {
      //Permission
      resource: 'financial',
      action: 'menu_costs',
    }
  },
  {
    path: '/apps/financial/cards',
    name: 'apps-financial-cards',
    component: () => import('@/views/apps/financial/cards/Card.vue'),
    meta: {
      //Permission
      resource: 'financial',
      action: 'menu_cards',
    }
  },
  {
    path: '/apps/financial/fees',
    name: 'apps-financial-fees',
    component: () => import('@/views/apps/financial/fees/Fee.vue'),
    meta: {
      //Permission
      resource: 'financial',
      action: 'menu_fees',
    }
  },

  // *===============================================---*
  // *--------- SETTINGS  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/settings/company',
    name: 'apps-settings-company',
    component: () => import('@/views/apps/settings/company/Company.vue'),
    meta: {
      //Permission
      resource: 'settings',
      action: 'menu_settings_company',
    }
  },

  // *===============================================---*
  // *--------- ADMINISTRATION  ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/administration/partners',
    name: 'apps-administration-partners',
    component: () => import('@/views/apps/administration/partners/Partner.vue'),
    meta: {
      //Permission
      resource: 'administration',
      action: 'menu_administration_partner',
    }
  },
  {
    path: '/apps/administration/partners/payment-orders',
    name: 'apps-administration-partners-payment-orders',
    component: () => import('@/views/apps/administration/partners/payment-order/PaymentOrder.vue'),
    meta: {
      //Permission
      resource: 'administration',
      action: 'menu_administration_partner',
    }
  },
  {
    path: '/apps/administration/partners/invoices',
    name: 'apps-administration-partners-invoices',
    component: () => import('@/views/apps/administration/partners/invoice/Invoice.vue'),
    meta: {
      //Permission
      resource: 'administration',
      action: 'menu_administration_partner',
    }
  },
  {
    path: '/apps/administration/companies',
    name: 'apps-administration-companies',
    component: () => import('@/views/apps/administration/companies/Company.vue'),
    meta: {
      //Permission
      resource: 'administration',
      action: 'menu_administration_company',
    }
  },
  {
    path: '/apps/administration/notifications',
    name: 'apps-administration-notifications',
    component: () => import('@/views/apps/administration/notifications/Notification.vue'),
    meta: {
      //Permission
      resource: 'administration',
      action: 'menu_administration_notification',
    }
  },
  {
    path: '/apps/administration/instances',
    name: 'apps-administration-instances',
    component: () => import('@/views/apps/administration/instances/Instance.vue'),
    meta: {
      //Permission
      resource: 'administration',
      action: 'menu_administration_notification',
    }
  },
  

  // *===============================================---*
  // *--------- USER ---- ---------------------------------------*
  // *===============================================---*
  {
    path: '/apps/users/list',
    name: 'apps-users-list',
    component: () => import('@/views/apps/user/users-list/UsersList.vue'),
  },
  {
    path: '/apps/users/view/:id',
    name: 'apps-users-view',
    component: () => import('@/views/apps/user/users-view/UsersView.vue'),
  },
  {
    path: '/apps/users/edit/:id',
    name: 'apps-users-edit',
    component: () => import('@/views/apps/user/users-edit/UsersEdit.vue'),
  },

  // Invoice
  {
    path: '/apps/invoice/list',
    name: 'apps-invoice-list',
    component: () => import('@/views/apps/invoice/invoice-list/InvoiceList.vue'),
  },
  {
    path: '/apps/invoice/preview/:id',
    name: 'apps-invoice-preview',
    component: () => import('@/views/apps/invoice/invoice-preview/InvoicePreview.vue'),
  },
  {
    path: '/apps/invoice/add/',
    name: 'apps-invoice-add',
    component: () => import('@/views/apps/invoice/invoice-add/InvoiceAdd.vue'),
  },
  {
    path: '/apps/invoice/edit/:id',
    name: 'apps-invoice-edit',
    component: () => import('@/views/apps/invoice/invoice-edit/InvoiceEdit.vue'),
  },
]
