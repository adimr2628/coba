app.controller("taccsuratkeluarCtrl", function($scope, Data, $rootScope, Upload, toaster, $uibModal) {
    /**
     * Inialisasi
     */
    var tableStateRef;
    $scope.formtittle = "";
    $scope.displayed = [];
    $scope.form = {};
    $scope.kode_surat = {};
    $scope.is_edit = false;
    $scope.is_view = false;
    $scope.is_create = false;
    $scope.loading = false;
    /**
     * End inialisasi
     */
    $scope.callServer = function callServer(tableState) {
        tableStateRef = tableState;
        $scope.isLoading = true;
        var offset = tableState.pagination.start || 0;
        var limit = tableState.pagination.number || 10;
        var param = {
            offset: offset,
            limit: limit
        };
        if (tableState.sort.predicate) {
            param["sort"] = tableState.sort.predicate;
            param["order"] = tableState.sort.reverse;
        }
        if (tableState.search.predicateObject) {
            param["filter"] = tableState.search.predicateObject;
        }
        Data.get("t_acc_surat/index", param).then(function(response) {
            $scope.displayed = response.data.list;
            $scope.kode_surat = response.data.kode_surat;
            console.log($scope.kode_surat);
            tableState.pagination.numberOfPages = Math.ceil(response.data.totalItems / limit);
        });
        $scope.isLoading = false;
    };
    $scope.cancel = function() {
        $scope.is_edit = false;
        $scope.is_view = false;
        $scope.is_create = false;
        $scope.callServer(tableStateRef);
    };
    $scope.tolak = function(row) {
        var passData = row;
        var modalInstance = $uibModal.open({
            templateUrl: "tpl/t_acc_surat/modalKeterangan.html",
            controller: "modalKetCtrl",
            size: "sm",
            backdrop: "static",
            resolve: {
                data: passData
            }
        }).result.then(function (response) {
            $scope.cancel();
        });
        // $scope.formApproval.status = 'Ditolak';
    };
    $scope.acc = function(row) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Apa anda yakin akan Menyetujui Pengajuan ini ?',
            type: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.value) {
                row.status = "acc";
                row.ket = "Disetujui oleh Direktur";
                Data.post('t_acc_surat/acc', row).then(function (response) {
                    if (response.status_code == 200) {
                        $rootScope.alert("Berhasil", "Data berhasil disetujui", "success");
                        $scope.cancel();
                    } else {
                        $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
                        $scope.cancel();
                    }
                });
            }
        });
    }
    $scope.downloadFile = function (file) {
        window.open("api/t_pengajuan_surat/download?file=" + file);
    }
});

app.controller('modalKetCtrl', function ($state, $scope, $rootScope, Data, $uibModalInstance, toaster, data, $sce) {

    console.log(data);
    $scope.req = data; 

    $scope.close = (val) => {
        $uibModalInstance.close();
    };

    $scope.save = () => {
        Swal.fire({
            title: 'Peringatan',
            text: 'Apa anda yakin akan Menolak pengajuan ini ?',
            type: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.value) {
                $scope.req.status = 'decline';
                Data.post("t_acc_surat/acc", $scope.req).then(function (response) {
                    if (response.status_code == 200) {
                        $rootScope.alert("Berhasil", "Data berhasil ditolak", "success");
                        $scope.cancel();
                    } else {
                        $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
                        $scope.cancel();
                    }
                });
                $uibModalInstance.close({});
            }
        });
    }
});
