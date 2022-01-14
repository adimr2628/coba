app.controller("lsuratmasukCtrl", function($scope, Data, $rootScope, toaster) {
    /**
     * Inialisasi
     */
    var tableStateRef;
    $scope.displayed = [];
    $scope.periode = {};
    $scope.is_view = false;
    $scope.filter = {};
    $scope.reset_filter = function() {
        $scope.filter = {};
    };
    $scope.view = function(data) {
        Data.get('l_surat_masuk/index', data).then(function(response) {
            if (response.status_code == 200) {
                $scope.periode = data.periode;
                $scope.is_view = true;
                $scope.laporan = response.data.list;
            } else {
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(response.errors), "error");
            }
        });
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
        var blob = new Blob([document.getElementById('print-area').innerHTML], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
        });
        saveAs(blob, "Laporan-Surat-Masuk.xls");
    };
});