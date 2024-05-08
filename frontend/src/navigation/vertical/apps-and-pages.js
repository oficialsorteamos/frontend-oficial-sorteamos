export default [
  /*{
    header: 'Apps & Pages',
  },*/
  /*{
    title: 'Email',
    route: 'apps-email',
    icon: 'MailIcon',
  },*/
  {
    title: 'Dashboard',
    route: 'apps-dashboard',
    icon: 'HomeIcon',
    resource: 'dashboard',
    action: 'menu_dashboard',
  },
  {
    title: 'Chat',
    route: 'apps-chat',
    icon: 'MessageSquareIcon',
    resource: 'chat',
    action: 'menu_chat',
  },
  {
    title: 'Chatbot',
    route: 'apps-chatbot',
    icon: 'CheckSquareIcon',
    resource: 'chatbot',
    action: 'menu_chatbot',
  },
  {
    title: 'Services',
    route: 'apps-services',
    icon: 'PhoneCallIcon',
    resource: 'service',
    action: 'menu_service',
  },
  {
    title: 'Calendar',
    route: 'apps-calendar',
    icon: 'CalendarIcon',
    resource: 'calendar',
    action: 'menu_calendar',
  },
  {
    title: 'Contacts',
    route: 'apps-contacts-list',
    icon: 'UserIcon',
    resource: 'contact',
    action: 'menu_contact',
  },
  {
    title: 'Integrations',
    icon: 'LayersIcon',
    children: [
      {
        title: 'Voip',
        route: 'apps-integrations-voip',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Dialers',
        route: 'apps-integrations-dialers',
        resource: 'management',
        action: 'menu_management',
      },
    ],
  },
  {
    title: 'Management',
    icon: 'PackageIcon',
    children: [
      {
        title: 'Channels',
        route: 'apps-management-channels',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Departments',
        route: 'apps-management-departments',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Tags',
        route: 'apps-management-tags',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Templates',
        route: 'apps-management-templates',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Users',
        route: 'apps-management-users',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'General Messages',
        route: 'apps-management-general-messages',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Blacklist',
        route: 'apps-management-blacklist',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Calls',
        route: 'apps-management-calls',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Extensions',
        route: 'apps-management-extensions',
        resource: 'management',
        action: 'menu_management',
      },
      {
        title: 'Permissions Control',
        route: 'apps-management-permissions-control',
        resource: 'administration',
        action: 'menu_administration_partner',
        children: [
          {
            title: 'Roles',
            route: 'apps-management-roles',
            resource: 'administration',
            action: 'menu_administration_partner',
          },
        ],
      },
    ],
  },
  {
    title: 'Campaigns',
    route: 'apps-campaign',
    icon: 'BriefcaseIcon',
    resource: 'campaign',
    action: 'menu_campaign',
  },
  {
    title: 'Reports',
    icon: 'PieChartIcon',
    children: [
      {
        title: 'Services',
        route: 'apps-reports-services',
        resource: 'reports',
        action: 'menu_reports_services',
      },
      {
        title: 'Operator Evaluation',
        route: 'apps-reports-operator-evaluation',
        resource: 'reports',
        action: 'menu_reports_services',
      },
    ],
  },
  {
    title: 'Notifications',
    route: 'apps-notifications',
    icon: 'BellIcon',
    resource: 'notification',
    action: 'menu_notification',
  },
  {
    title: 'Financial',
    icon: 'DollarSignIcon',
    children: [
      {
        title: 'Invoices',
        route: 'apps-financial-invoices',
        resource: 'financial',
        action: 'menu_invoices',
      },
      {
        title: 'Credits',
        route: 'apps-financial-credits',
        resource: 'financial',
        action: 'menu_credits',
      },
      {
        title: 'Costs',
        route: 'apps-financial-costs',
        resource: 'financial',
        action: 'menu_costs',
      },
      {
        title: 'Cards',
        route: 'apps-financial-cards',
        resource: 'financial',
        action: 'menu_cards',
      },
      {
        title: 'Fees',
        route: 'apps-financial-fees',
        resource: 'financial',
        action: 'menu_fees',
      },
    ],
  },
  {
    title: 'Settings',
    icon: 'SettingsIcon',
    children: [
      {
        title: 'Company',
        route: 'apps-settings-company',
        resource: 'settings',
        action: 'menu_settings_company',
      },
    ],
  },
  
  {
    title: 'Administration',
    icon: 'BookIcon',
    children: [
      {
        title: 'Partnership',
        route: 'apps-administration-partners',
        resource: 'administration',
        action: 'menu_administration_partner',
        children: [
          {
            title: 'Partners',
            route: 'apps-administration-partners',
            resource: 'administration',
            action: 'menu_administration_partner',
          },
          {
            title: 'Orders Payment',
            route: 'apps-administration-partners-payment-orders',
            resource: 'administration',
            action: 'menu_administration_partner',
          },
          {
            title: 'Invoices',
            route: 'apps-administration-partners-invoices',
            resource: 'administration',
            action: 'menu_administration_partner',
          },
        ],
      },
      {
        title: 'Companies',
        route: 'apps-administration-companies',
        resource: 'administration',
        action: 'menu_administration_company',
      },
      {
        title: 'Notifications',
        route: 'apps-administration-notifications',
        resource: 'administration',
        action: 'menu_administration_notification',
      },
      {
        title: 'Instances',
        route: 'apps-administration-instances',
        resource: 'administration',
        action: 'menu_administration_notification',
      },
    ],
  },
  
  /*
  {
    title: 'Invoice',
    icon: 'FileTextIcon',
    children: [
      {
        title: 'List',
        route: 'apps-invoice-list',
      },
      {
        title: 'Preview',
        route: { name: 'apps-invoice-preview', params: { id: 4987 } },
      },
      {
        title: 'Edit',
        route: { name: 'apps-invoice-edit', params: { id: 4987 } },
      },
      {
        title: 'Add',
        route: { name: 'apps-invoice-add' },
      },
    ],
  },*/
  /*
  {
    title: 'eCommerce',
    icon: 'ShoppingCartIcon',
    children: [
      {
        title: 'Shop',
        route: 'apps-e-commerce-shop',
      },
      {
        title: 'Details',
        route: { name: 'apps-e-commerce-product-details', params: { slug: 'apple-watch-series-5-27' } },
      },
      {
        title: 'Wishlist',
        route: 'apps-e-commerce-wishlist',
      },
      {
        title: 'Checkout',
        route: 'apps-e-commerce-checkout',
      },
    ],
  },*/
  /*
  {
    title: 'User',
    icon: 'UserIcon',
    children: [
      {
        title: 'List',
        route: 'apps-users-list',
      },
      {
        title: 'View',
        route: { name: 'apps-users-view', params: { id: 21 } },
      },
      {
        title: 'Edit',
        route: { name: 'apps-users-edit', params: { id: 21 } },
      },
    ],
  },*/
  /*
  {
    title: 'Pages',
    icon: 'FileIcon',
    children: [
      {
        title: 'Authentication',
        icon: 'CircleIcon',
        children: [
          {
            title: 'Login v1',
            route: 'auth-login-v1',
            target: '_blank',
          },
          {
            title: 'Login v2',
            route: 'auth-login-v2',
            target: '_blank',
          },
          {
            title: 'Register v1',
            route: 'auth-register-v1',
            target: '_blank',
          },
          {
            title: 'Register v2',
            route: 'auth-register-v2',
            target: '_blank',
          },
          {
            title: 'Forgot Password v1',
            route: 'auth-forgot-password-v1',
            target: '_blank',
          },
          {
            title: 'Forgot Password v2',
            route: 'auth-forgot-password-v2',
            target: '_blank',
          },
          {
            title: 'Reset Password v1',
            route: 'auth-reset-password-v1',
            target: '_blank',
          },
          {
            title: 'Reset Password v2',
            route: 'auth-reset-password-v2',
            target: '_blank',
          },
        ],
      },
      {
        title: 'Account Settings',
        route: 'pages-account-setting',
      },
      {
        title: 'Profile',
        route: 'pages-profile',
      },
      {
        title: 'Faq',
        route: 'pages-faq',
      },
      {
        title: 'Knowledge Base',
        route: 'pages-knowledge-base',
      },
      {
        title: 'Pricing',
        route: 'pages-pricing',
      },
      {
        title: 'Blog',
        children: [
          {
            title: 'List',
            route: 'pages-blog-list',
          },
          {
            title: 'Detail',
            route: { name: 'pages-blog-detail', params: { id: 1 } },
          },
          {
            title: 'Edit',
            route: { name: 'pages-blog-edit', params: { id: 1 } },
          },
        ],
      },
      {
        title: 'Mail Templates',
        children: [
          {
            title: 'Welcome',
            href: 'https://pixinvent.com/demo/vuexy-mail-template/mail-welcome.html',
          },
          {
            title: 'Reset Password',
            href: 'https://pixinvent.com/demo/vuexy-mail-template/mail-reset-password.html',
          },
          {
            title: 'Verify Email',
            href: 'https://pixinvent.com/demo/vuexy-mail-template/mail-verify-email.html',
          },
          {
            title: 'Deactivate Account',
            href: 'https://pixinvent.com/demo/vuexy-mail-template/mail-deactivate-account.html',
          },
          {
            title: 'Invoice',
            href: 'https://pixinvent.com/demo/vuexy-mail-template/mail-invoice.html',
          },
          {
            title: 'Promotional',
            href: 'https://pixinvent.com/demo/vuexy-mail-template/mail-promotional.html',
          },
        ],
      },
      {
        title: 'Miscellaneous',
        icon: 'CircleIcon',
        children: [
          {
            title: 'Coming Soon',
            route: 'misc-coming-soon',
            target: '_blank',
          },
          {
            title: 'Not Authorized',
            route: 'misc-not-authorized',
            target: '_blank',
          },
          {
            title: 'Under Maintenance',
            route: 'misc-under-maintenance',
            target: '_blank',
          },
          {
            title: 'Error',
            route: 'misc-error',
            target: '_blank',
          },
        ],
      },
    ],
  },*/
]
