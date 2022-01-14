angular.module('app').controller('dashboardCtrl', function($scope, Data, $state, UserService, $location) {
    var user = UserService.getUser();
    if (user === null) {
        $location.path('/login');
    }

    // initialization
    $scope.jumlahSuratMasuk;
    $scope.jumlahSuratKeluar;
    $scope.jumlahPengguna;

    Data.get('site/dashboard').then(function (response) {
    	if (response.status_code == 200) {
    		$scope.jumlahSuratMasuk = response.data.jumlahSuratMasuk;
    		$scope.jumlahSuratKeluar = response.data.jumlahSuratKeluar;
    		$scope.jumlahPengguna = response.data.jumlahPengguna;
            $scope.jumlahPengajuan = response.data.jumlahPengajuan;
            $scope.jumlahDisposisi = response.data.jumlahDisposisi;
    	}
    });
});