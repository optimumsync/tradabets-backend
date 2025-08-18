<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Settings
    |--------------------------------------------------------------------------
    |
    | This file is for all the menu specific settings.
    |
    */

    'main-menu-settings' => [
        'top-level' => [
            'ul' => [
                'class' => 'nav nav-main'
            ],
            'li' => [
                'has-children-class' => 'nav-parent'
            ],
            'a' => [
                'class' => 'nav-link',
            ],
        ],
        'sub-level' => [
            'ul' => [
                'class' => 'nav nav-children'
            ],
            'li' => [
                'has-children-class' => 'nav-parent'
            ],
            'a' => [
                'class' => 'nav-link',
            ],
        ],
    ],

    'main-menu' => [
       /* [
            'uri' => '/dashboard',
            'prepend-title' => '<i class="fas fa-home" aria-hidden="true"></i>',
            'title' => '<span>Dashboard</span>',
        ],*/
        /*[
            'uri' =>'/#',
             'prepend-title' => '<i class="fas fa-home" aria-hidden="true"></i>',
            'title' => '<span></span>',
        ],*/


        //Bet List
        [
            'uri' => '/betlist',
            'title' => '<span>Bet List</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/betlist.png"/>',
        ],

      //Bet List Cashout
       [
            'uri' => '/betlist-cashout',
            'title' => '<span>Bet List Cashout</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/betlistcashout.png"/>',
        ],
        //Transaction List
        [
            'uri' => '/transaction',
            'title' => '<span>Transaction List</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/transactionlist.png"/>',
        ],
        // [
        //     'uri' => '/active-bonus',
        //     'title' => '<span>Active Bonus</span>',
        //     'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/activebbonus.png"/>',
        // ],
        // [
        //     'uri' => '/rewards',
        //     'title' => '<span>Rewards</span>',
        //     'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/rewards.png"/>',
        // ],
        [
            'uri' => '/bonus-transaction-list',
            'title' => '<span>Bonus Transaction List</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/bonustransactionlist.png"/>',
        ],
        [
            'uri' => '/inbox',
            'title' => '<span>Messages</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/messages.png"/>',
			'session-badge' => [
				'session-var' => 'num_inbox_notifications',
				'badge-html' => '<span class="float-right badge badge-primary">[val]</span>',
			]
        ],
        [
            'uri' => '/deposits',
            'title' => '<span>Deposit</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/deposits.png"/>',
        ],
        [
            'uri' => '/withdraw',
            'title' => '<span>WithDraw</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/withdraw.png"/>',
        ],
        // [
        //     'uri' => '/document-upload',
        //     'title' => '<span>KYC</span>',
        //     'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/kycinfo.png"/>',
        // ],
        [
            'uri' => '/bank-accounts',
            'title' => '<span>Bank Accounts</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/kycinfo.png"/>',
        ],

        //AnswerType


        // inbox


        // master data
       /*[
            'uri' => '/master-data',
            'title' => '<span>Master Data</span>',
            'prepend-title' => '<i class="fas fa-align-center" aria-hidden="true"></i>',
            'sub-links' => [
                [
                    'uri' => '/master-data/AnswerType',
                    'title' => ' - Answer Type'
                ],
                [
                    'uri' => '/master-data/AssessmentType',
                    'title' => ' - Assessment Type'
                ],
                [
                    'uri' => '/master-data/Facilities',
                    'title' => ' - Facilities'
                ],
                [
                    'uri' => '/master-data/FormField',
                    'title' => ' - Form Fields'
                ],
                [
                    'uri' => '/master-data/Questions',
                    'title' => ' - Questions'
                ],
                [
                    'uri' => '/master-data/QuestionGroup',
                    'title' => ' - Question Group'
                ],
                [
                    'uri' => '/master-data/QuestionType',
                    'title' => ' - Question Type'
                ],
                [
                    'uri' => '/master-data/StdFacilityArea',
                    'title' => ' - Std Facility Areas'
                ],


            ],
        ],*/

        // users
       /* [
            'uri' => '/security-groups&&/users',
            'title' => '<span>Security</span>',
            'prepend-title' => '<i class="fas fa-lock" aria-hidden="true"></i>',
            'sub-links' => [
                [
                    'uri' => '/security-groups',
                    'title' => ' - Security Groups',
                ],
                [
                    'uri' => '/users',
                    'title' => ' - Users'
                ],
            ],
        ],*/

       /* // my content
        [
            'uri' => '/user-content',
            'title' => '<span>My Content</span>',
            'prepend-title' => '<i class="fas fa-file" aria-hidden="true"></i>',
        ],*/
        //Legal
/*
          [
            'uri' => '/user-privacy-policy',
            'title' => '<span>Legal</span>',
            'prepend-title' => '<i class="fas fa-align-center" aria-hidden="true"></i>',
            'sub-links' => [
		// privacy policy
        [
            'uri' => '/user-privacy-policy',
            'title' => '<span>Privacy Policy</span>',
            'prepend-title' => '<i class="fas fa-minus-circle" aria-hidden="true"></i>',
        ],

		// terms and conditions
        [
            'uri' => '/user-terms-and-conditions',
            'title' => '<span>Terms And Conditions</span>',
            'prepend-title' => '<i class="fas fa-balance-scale" aria-hidden="true"></i>',
        ],
    ],
],*/

	/*	// api
        [
            'uri' => '/api-docs/v1/auth',
            'title' => '<span>API</span>',
            'prepend-title' => '<i class="fas fa-code-branch" aria-hidden="true"></i>',
        ],*/

       /* [
            'uri' => '/logout',
            'a-class' => 'is-logout-link',
            'prepend-title' => '<i class="fas fa-power-off" aria-hidden="true"></i>',
            'title' => '<span>Logout</span>',
        ],*/
    ],
    'admin-menu' => [
        [
            'uri' => '/transaction-view',
            'prepend-title' => '<i class="fas fa-file" aria-hidden="true"></i>',
            'title' => '<span>Users-Transaction-list</span>',
        ],
        [
            'uri' => '/balance-view',
            'prepend-title' => '<i class="fas fa-file" aria-hidden="true"></i>',
            'title' => '<span>Users-Balance-list</span>',
        ],
        [
            'uri' => '/withdraw-requests',
            'prepend-title' => '<i class="fas fa-file" aria-hidden="true"></i>',
            'title' => '<span>Users-Withdraw-Requests</span>',
        ],
         [
             'uri' => '/kyc-list',
             'prepend-title' => '<i class="fas fa-file" aria-hidden="true"></i>',
             'title' => '<span>Users-KYC-list</span>',
         ],

        [
            'uri' => '/inbox',
            'title' => '<span>Messages</span>',
            'prepend-title' => '<i class="fas fa-envelope" aria-hidden="true"></i>',
            'session-badge' => [
                'session-var' => 'num_inbox_notifications',
                'badge-html' => '<span class="float-right badge badge-primary">[val]</span>',
            ]
        ],
        [
            'uri' => '/transaction-report',
            'prepend-title' => '<i class="fas fa-file" aria-hidden="true"></i>',
            'title' => '<span>Payment-Transaction-Report</span>',
        ],
        [
            'uri' => '/user-list',
            'prepend-title' => '<i class="fas fa-user" aria-hidden="true"></i>',
            'title' => '<span>User-list</span>',
        ],
         //Bet List
        [
            'uri' => '/betlist',
            'title' => '<span>Bet List</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/betlist.png"/>',
        ],
        //Bet List Cashout
       [
            'uri' => '/betlist-cashout',
            'title' => '<span>Bet List Cashout</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/betlistcashout.png"/>',
       ],
         [
            'uri' => '/admin/bonuses',
            'title' => '<span>Manage Bonus</span>',
            'prepend-title' => '<img class="user-menu-icons" src="/themes/admin/img/betlistcashout.png"/>',
       ],
       [
    'uri' => '/withdraw-list',
    'prepend-title' => '<i class="fas fa-money-bill-alt" aria-hidden="true"></i>',
    'title' => '<span>Withdrawal List</span>',
],
       

        ],

];