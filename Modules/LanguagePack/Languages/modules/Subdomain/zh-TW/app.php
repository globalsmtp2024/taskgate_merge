<?php 
return [
  'core' => [
    'subdomain' => 'Subdomain',
    'domain' => 'Domain',
    'customDomain' => 'Custom Domain',
    'domainType' => 'Domain Type',
    'continue' => 'Continue',
    'backToSignin' => 'Go Back to Sign In page',
    'alreadyKnow' => 'Oh, I just remembered the URL!',
    'workspaceTitle' => 'Sign in to your company url',
    'forgotCompanyTitle' => 'Find your company login url',
    'signInTitle' => 'Don’t know your company\'s login URL?',
    'signInTitleDescription' => 'Welcome to the login page! Please enter your credentials to access your account and start using the platform\'s features. If you don\'t have an account yet, you can easily sign up for one.',
    'bannedSubdomains' => 'Enter the list of subdomains you want to restrict from getting registered',
    'sendDomainNotification' => 'Send Domain Notification',
    'enterYourSubdomain' => 'Enter your subdomain to get started',
    'dontHaveAccount' => 'Don\'t have account? <b>Click to Sign up</b>',
    'companyNotFound' => 'COMPANY DOES NOT EXISTS FOR THAT URL',
  ],
  'messages' => [
    'forgetMailSuccess' => 'Please check your email. We have sent an email with your login url',
    'forgetMailFail' => 'Your provided email is not found. Please provide a valid email address.',
    'forgotPageMessage' => 'We will send a confirmation email to you in order to verify your email address and determine the presence of a pre-existing company URL.',
    'findCompanyUrl' => 'Find your company\'s login URL',
    'deleteSubdomain' => 'Are you sure you want to delete',
    'notAllowedToUseThisSubdomain' => 'Sorry, You are not allowed to use this subdomain',
    'noCompanyLined' => 'No company linked with this email',
    'notifyAllAdmins' => 'This will notify all admins their domain urls',
  ],
  'email' => [
    'subject' => 'Important Update: New Login URL for Your Company',
    'line2' => 'Welcome ',
    'line3' => 'We would like to inform you that the login URL for your company has been changed. Please take note of the new login URL and use it going forward.',
    'line4' => 'We apologize for any inconvenience this may have caused, but rest assured that the new URL has been implemented for enhanced security and easier access to your account.',
    'line5' => 'If you have any questions or concerns, please don\'t hesitate to reach out to our support team. We are always here to help. ',
    'noteLoginUrlChanged' => 'Login URL: ',
    'noteLoginUrl' => 'Please note your Login URL ',
    'thankYou' => 'Thank you for your continued business. ',
  ],
  'emailSuperAdmin' => [
    'subject' => 'New Superadmin Login URL- Subdomain Module Activation',
    'line3' => 'This is to inform you that the Superadmin Login URL has been updated following the activation of the <strong>Subdomain Module</strong>. The new URL is now ',
    'noteLoginUrlChanged' => 'Superadmin Login URL: ',
    'noteLoginUrl' => 'Please note your Superadmin Login URL ',
  ],
  'match' => [
    'title' => '您甚至可以遵循以下模式',
    'pattern' => '<p>1. <b>測試</b>（完全匹配）</p>
                            <p>2. <b>%test%</b>（符合字串中的任意位置）</p>
                            <p>3. <b>%test</b>（符合任意位置，但必須以「test」結尾）</p>',
  ],
];