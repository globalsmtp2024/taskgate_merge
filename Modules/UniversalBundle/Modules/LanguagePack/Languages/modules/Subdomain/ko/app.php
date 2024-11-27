<?php 
return [
  'core' => [
    'subdomain' => '하위 도메인',
    'domain' => '도메인',
    'customDomain' => '맞춤 도메인',
    'domainType' => '도메인 유형',
    'continue' => '계속하다',
    'backToSignin' => '로그인 페이지로 돌아가기',
    'alreadyKnow' => '아, 방금 URL이 생각났어요!',
    'workspaceTitle' => '회사 URL에 로그인하세요.',
    'forgotCompanyTitle' => '회사 로그인 URL 찾기',
    'signInTitle' => '회사의 로그인 URL을 모르시나요?',
    'signInTitleDescription' => '로그인 페이지에 오신 것을 환영합니다! 계정에 액세스하고 플랫폼 기능을 사용하려면 자격 증명을 입력하세요. 아직 계정이 없다면, 쉽게 가입할 수 있습니다.',
    'bannedSubdomains' => '등록을 제한하려는 하위 도메인 목록을 입력하세요.',
    'sendDomainNotification' => '도메인 알림 보내기',
    'enterYourSubdomain' => '시작하려면 하위 도메인을 입력하세요.',
    'dontHaveAccount' => '계정이 없나요? <b>가입하려면 클릭하세요</b>',
    'companyNotFound' => '해당 URL에는 회사가 존재하지 않습니다.',
  ],
  'messages' => [
    'forgetMailSuccess' => '이메일을 확인해주세요. 귀하의 로그인 URL이 포함된 이메일을 보냈습니다.',
    'forgetMailFail' => '귀하가 제공한 이메일을 찾을 수 없습니다. 유효한 이메일 주소를 입력하세요.',
    'forgotPageMessage' => '귀하의 이메일 주소를 확인하고 기존 회사 URL이 있는지 확인하기 위해 확인 이메일을 보내드립니다.',
    'findCompanyUrl' => '회사의 로그인 URL 찾기',
    'deleteSubdomain' => '삭제 하시겠습니까',
    'notAllowedToUseThisSubdomain' => '죄송합니다. 이 하위 도메인을 사용할 수 없습니다.',
    'noCompanyLined' => '이 이메일과 연결된 회사가 없습니다.',
    'notifyAllAdmins' => '그러면 모든 관리자에게 도메인 URL이 통보됩니다.',
  ],
  'email' => [
    'subject' => '중요 업데이트: 회사의 새로운 로그인 URL',
    'line2' => '환영',
    'line3' => '귀사의 로그인 URL이 변경되었음을 알려드립니다. 새로운 로그인 URL을 기록해 두시고 앞으로도 활용해 주시기 바랍니다.',
    'line4' => '이로 인해 불편을 끼쳐드려 죄송합니다. 보안을 강화하고 계정에 더 쉽게 액세스할 수 있도록 새 URL이 구현되었으니 안심하시기 바랍니다.',
    'line5' => '질문이나 우려사항이 있는 경우 주저하지 말고 지원팀에 문의하세요. 우리는 항상 도움을 드리고 있습니다.',
    'noteLoginUrlChanged' => '로그인 URL:',
    'noteLoginUrl' => '로그인 URL을 적어주세요',
    'thankYou' => '지속적인 사업에 감사드립니다.',
  ],
  'emailSuperAdmin' => [
    'subject' => '새로운 최고 관리자 로그인 URL - 하위 도메인 모듈 활성화',
    'line3' => '<strong>하위 도메인 모듈</strong> 활성화 이후 최고 관리자 로그인 URL이 업데이트되었음을 ​​알려드립니다. 이제 새 URL은 다음과 같습니다.',
    'noteLoginUrlChanged' => '최고관리자 로그인 URL:',
    'noteLoginUrl' => '최고관리자 로그인 URL을 적어주세요',
  ],
  'match' => [
    'title' => '아래 패턴을 따를 수도 있습니다.',
    'pattern' => '<p>1. <b>테스트</b>(정확히 일치)</p>
                            <p>2. <b>%test%</b>(문자열의 어느 위치에서나 일치)</p>
                            <p>3. <b>%test</b>(어디에서나 일치하지만 \'test\'로 끝나야 함)</p>',
  ],
];