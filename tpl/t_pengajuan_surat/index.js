app.controller("treqsuratkeluarCtrl", function($scope, Data, $rootScope, Upload) {
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
        Data.get("t_pengajuan_surat/index", param).then(function(response) {
            $scope.displayed = response.data.list;
            $scope.kode_surat = response.data.kode_surat;
            tableState.pagination.numberOfPages = Math.ceil(response.data.totalItems / limit);
        });
        $scope.isLoading = false;
    };

    $scope.clearFilter = function (filter) {
        var param = tableStateRef.search.predicateObject;
        if (filter == 'tgl_surat') {
            param.tgl_surat = "";
        }else if (filter == 'tgl_diterima') {
            param.tgl_diterima = "";
        }
        tableStateRef.search.predicateObject = param;
        $scope.callServer(tableStateRef);
    }

    $scope.create = function(form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = true;
        $scope.formtittle = "Form Tambah Data";
        $scope.form = {};
    };

    $scope.update = function(form) {
        $scope.is_edit      = true;
        $scope.is_create    = false;
        $scope.is_view      = false;
        $scope.formtittle   = "Edit Data : " + form.no_surat;
        $scope.form         = form;
    };

    $scope.view = function(form) {
        $scope.is_edit      = true;
        $scope.is_create    = false;
        $scope.is_view      = true;
        $scope.formtittle   = "Lihat Data : " + form.no_surat;
        $scope.form         = form;
    };

    $scope.save = function(form) {
        $scope.loading = true;
        form.tgl_surat = new Date();
        Upload.upload({
            url: 'http://localhost/ams/api/t_pengajuan_surat/save',
            data: {
                file: $scope.f, 
                'form': form
            },
        }).then(function onSuccess(result) {
            if (result.data.status_code == 200) {
                $rootScope.alert("Berhasil", "Data berhasil disimpan", "success");
                $scope.cancel();
            } else {
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
            }
        }).catch(function onError(result) {
            $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
        });
        $scope.loading = false;
    };

    $scope.request = function (form) {
        $scope.loading = true;
        form.status = 'request';
        Upload.upload({
            url: 'http://localhost/ams/api/t_pengajuan_surat/save',
            data: {
                file: $scope.f, 
                'form': form
            },
        }).then(function onSuccess(result) {
            if (result.data.status_code == 200) {
                $rootScope.alert("Berhasil", "Data berhasil disimpan", "success");
                $scope.cancel();
            } else {
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
            }
        }).catch(function onError(result) {
            $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
        });
        $scope.loading = false;
    }

    $scope.uploadFile = function(file, errFiles) {
        $scope.f = file;
        $scope.errFile = errFiles && errFiles[0];
        if (file) {} else {
            $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.errors), "error");
        }
    };

    $scope.cancel = function() {
        $scope.is_edit = false;
        $scope.is_view = false;
        $scope.is_create = false;
        $scope.callServer(tableStateRef);
    };

    $scope.delete = function(row) {
        if (confirm("Apa anda yakin akan Menghapus item ini ?")) {
            row.is_deleted = 0;
            Data.post("t_pengajuan_surat/hapus", row).then(function(result) {
                $scope.displayed.splice($scope.displayed.indexOf(row), 1);
            });
        }
    };

    $scope.downloadFile = function (file) {
        window.open("api/t_pengajuan_surat/download?file=" + file);
    }

    $scope.downloadTemplate = function () {
        window.open("api/t_pengajuan_surat/downloadTemplate");
    }
});