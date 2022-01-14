app.controller("lsuratkeluarCtrl", function($scope, Data, $rootScope) {
    /**
     * Inialisasi
     */
    var tableStateRef;
    $scope.laporan = [];
    $scope.listPegawai = [];
    $scope.filter = {};
    $scope.is_view = false;
    $scope.reset_filter = function() {
        $scope.filter = {};
    };
    $scope.getPemohon = function (nama) {
        let filter = {
            filter: {
                nama: nama
            }
        }
        Data.get('l_surat_keluar/getPemohon', filter).then(function (response) {
            if (response.status_code) {
                $scope.listPegawai = response.data.list;
            }
        });
    };
    $scope.setPemohon = function (row) {
        console.log(row);
        console.log($scope.filter);
        // $scope.filter.user = row;
    }
    $scope.view = function(filter) {
        if (filter.user != undefined) {
            filter.user_id = filter.user.id
        }
        Data.get('l_surat_keluar/index', filter).then(function (response) {
            if (response.status_code == 200) {
                $scope.is_view = true;
                $scope.periode = filter.periode;
                $scope.laporan = response.data.list;
            } else {
                $scope.is_view = false;
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(response.errors) ,"error");
            }
        })
    };
    $scope.print = function() {
        var printContents = document.getElementById('print-area').innerHTML;
        var popupWin = window.open('', '_blank', 'width=1000,height=700');
        popupWin.document.open();
        popupWin.document.write(`<html><head>
        <link rel="stylesheet" type="text/css" href="./tpl/common/print-style.css" />
        </head>
        <body onload="window.print()">` + printContents + `</body></html>`);
        popupWin.document.close();
    };
    $scope.export = function() {
        console.log('asdas');
        var blob = new Blob([document.getElementById('print-area').innerHTML], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
        });
        saveAs(blob, "Laporan-Surat-Keluar.xls");
    };
});