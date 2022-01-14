angular.module("app").config(["$stateProvider", "$urlRouterProvider", "$ocLazyLoadProvider", "$breadcrumbProvider",
    function($stateProvider, $urlRouterProvider, $ocLazyLoadProvider, $breadcrumbProvider) {
        $urlRouterProvider.otherwise("/dashboard");
        $ocLazyLoadProvider.config({
            debug: false
        });
        $breadcrumbProvider.setOptions({
            prefixStateName: "app.main",
            includeAbstract: true,
            template: '<li class="breadcrumb-item" ng-repeat="step in steps" ng-class="{active: $last}" ng-switch="$last || !!step.abstract"><a ng-switch-when="false" href="{{step.ncyBreadcrumbLink}}">{{step.ncyBreadcrumbLabel}}</a><span ng-switch-when="true">{{step.ncyBreadcrumbLabel}}</span></li>'
        });
        $stateProvider.state("app", {
            abstract: true,
            templateUrl: "tpl/common/layouts/full.html",
            ncyBreadcrumb: {
                label: "Root",
                skip: true
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["fontawesome", "simplelineicon"]);
                    }
                ],
            }
        }).state("app.main", {
            url: "/dashboard",
            templateUrl: "tpl/dashboard/dashboard.html",
            ncyBreadcrumb: {
                label: "Home"
            },
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["chart.js"]).then(() => {
                            return $ocLazyLoad.load({
                                files: ["tpl/dashboard/dashboard.js"]
                            });
                        });
                    }
                ]
            }
        }).state("app.generator", {
            url: "/generator",
            templateUrl: "tpl/generator/index.html",
            ncyBreadcrumb: {
                label: "Home"
            },
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["chart.js"]).then(() => {
                            return $ocLazyLoad.load({
                                files: ["tpl/generator/index.js"]
                            });
                        });
                    }
                ]
            }
        }).state("app.pemasukan", {
            url: "/pemasukan",
            templateUrl: "api/vendor/cahkampung/landa-acc/tpl/t_pemasukan/index.html",
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["api/vendor/cahkampung/landa-acc/tpl/t_pemasukan/index.js"]
                        });
                    }
                ]
            }
        }).state("pengguna", {
            abstract: true,
            templateUrl: "tpl/common/layouts/full.html",
            ncyBreadcrumb: {
                label: "Master"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["fontawesome", "simplelineicon", "iconflag"]);
                    }
                ],
                loadPlugin: ["$ocLazyLoad", function($ocLazyLoad) {}],
                authenticate: authenticate
            }
        }).state("pengguna.akses", {
            url: "/jabatan",
            templateUrl: "tpl/m_akses/index.html",
            ncyBreadcrumb: {
                label: "Jabatan"
            },
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/m_akses/index.js"]
                        });
                    }
                ]
            }
        }).state("pengguna.user", {
            url: "/user",
            templateUrl: "tpl/m_user/index.html",
            ncyBreadcrumb: {
                label: "Pengguna"
            },
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/m_user/index.js"]
                        });
                    }
                ]
            }
        }).state("pengguna.profil", {
            url: "/profil",
            templateUrl: "tpl/m_user/profile.html",
            ncyBreadcrumb: {
                label: "Profil Pengguna"
            },
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/m_user/profile.js"]
                        });
                    }
                ]
            }
        }).state("page", {
            abstract: true,
            templateUrl: "tpl/common/layouts/blank.html",
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["fontawesome", "simplelineicon"]);
                    }
                ]
            }
        }).state("page.login", {
            url: "/login",
            templateUrl: "tpl/common/pages/login.html",
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/site/login.js"]
                        });
                    }
                ]
            }
        }).state("master", {
            abstract: true,
            templateUrl: "tpl/common/layouts/full.html",
            ncyBreadcrumb: {
                label: "Master"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["fontawesome", "simplelineicon", "iconflag"]);
                    }
                ],
                loadPlugin: ["$ocLazyLoad", function($ocLazyLoad) {}],
                authenticate: authenticate
            }
        }).state("master.kode", {
            url: "/kode",
            templateUrl: "tpl/m_kode/index.html",
            ncyBreadcrumb: {
                label: "Kode Surat"
            },
            resolve: {
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/m_kode/index.js"]
                        });
                    }
                ]
            }
        }).state("transaksi", {
            abstract: true,
            templateUrl: "tpl/common/layouts/full.html",
            ncyBreadcrumb: {
                label: "Transaksi"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["fontawesome", "simplelineicon", "iconflag"]);
                    }
                ],
                loadPlugin: ["$ocLazyLoad", function($ocLazyLoad) {}],
                authenticate: authenticate
            }
        }).state("transaksi.surat_masuk", {
            url: "/surat-masuk",
            templateUrl: "tpl/t_surat_masuk/index.html",
            ncyBreadcrumb: {
                label: "Surat Masuk"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/t_surat_masuk/index.js"]
                        });
                    }
                ]
            }
        }).state("transaksi.antrian", {
            url: "/antrian",
            templateUrl: "tpl/t_antrian/index.html",
            ncyBreadcrumb: {
                label: "Antrian Surat Masuk"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/t_antrian/index.js"]
                        });
                    }
                ]
            }
        }).state("transaksi.disposisi", {
            url: "/disposisi",
            templateUrl: "tpl/t_surat_masuk_disposisi/index.html",
            ncyBreadcrumb: {
                label: "Disposisi"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/t_surat_masuk_disposisi/index.js"]
                        });
                    }
                ]
            }
        }).state("transaksi.request_keluar", {
            url: "/req-surat",
            templateUrl: "tpl/t_pengajuan_surat/index.html",
            ncyBreadcrumb: {
                label: "Pengajuan Surat Keluar"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/t_pengajuan_surat/index.js"]
                        });
                    }
                ]
            }
        }).state("transaksi.acc_keluar", {
            url: "/acc-surat",
            templateUrl: "tpl/t_acc_surat/index.html",
            ncyBreadcrumb: {
                label: "Penyetujuan Surat Keluar"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/t_acc_surat/index.js"]
                        });
                    }
                ]
            }
        }).state("laporan", {
            abstract: true,
            templateUrl: "tpl/common/layouts/full.html",
            ncyBreadcrumb: {
                label: "Laporan"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load(["fontawesome", "simplelineicon", "iconflag"]);
                    }
                ],
                loadPlugin: ["$ocLazyLoad", function($ocLazyLoad) {}],
                authenticate: authenticate
            }
        }).state("laporan.surat_masuk", {
            url: "/laporan-surat-masuk",
            templateUrl: "tpl/l_surat_masuk/index.html",
            ncyBreadcrumb: {
                label: "Laporan Surat Masuk"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/l_surat_masuk/index.js"]
                        });
                    }
                ]
            }
        }).state("laporan.surat_keluar", {
            url: "/laporan-surat-keluar",
            templateUrl: "tpl/l_surat_keluar/index.html",
            ncyBreadcrumb: {
                label: "Laporan Surat Keluar"
            },
            resolve: {
                loadCSS: ["$ocLazyLoad",
                    function ($ocLazyLoad) {
                        return $ocLazyLoad.load(["ngFileUpload"]);
                    }
                ],
                loadMyCtrl: ["$ocLazyLoad",
                    function($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            files: ["tpl/l_surat_keluar/index.js"]
                        });
                    }
                ]
            }
        }).state("page.404", {
            url: "/404",
            templateUrl: "tpl/common/pages/404.html"
        }).state("page.500", {
            url: "/500",
            templateUrl: "tpl/common/pages/500.html"
        });

        function authenticate($q, UserService, $state, $transitions, $location, $rootScope) {
            var deferred = $q.defer();
            if (UserService.isAuth()) {
                deferred.resolve();
                var fromState = $state;
                var globalmenu = ["page.login", "pengguna.profil", "app.main", "page.500", "app.generator"];
                $transitions.onStart({}, function($transition$) {
                    var toState = $transition$.$to();
                    if ($rootScope.user.akses[toState.name.replace(".", "_")] || globalmenu.indexOf(toState.name)) {} else {
                        $state.target("page.500")
                    }
                });
            } else {
                $location.path("/login");
            }
            return deferred.promise;
        }
    }
]);