<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BrandModel;
use App\Models\CarImageModel;
use App\Models\CarModel;
use App\Models\CarTypeModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/upload.php';

/**
 * Quan ly xe. Hanh vi giu dung nhu cac file cu
 * CarRental_Admin/cars/{list,add,edit}.php +
 * CarRental_Backend/api/admin/cars/{store,update,delete,update_image,
 * set_main_image,delete_image}.php - chi doi cach to chuc.
 */
class CarController extends Controller
{
    private CarModel $carModel;
    private CarImageModel $carImageModel;
    private BrandModel $brandModel;
    private CarTypeModel $carTypeModel;

    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const ALLOWED_IMAGE_TYPES = ['gallery', 'front', 'back', 'interior'];

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/login');
        $this->carModel = new CarModel();
        $this->carImageModel = new CarImageModel();
        $this->brandModel = new BrandModel();
        $this->carTypeModel = new CarTypeModel();
    }

    public function index(): void
    {
        $keyword = $this->input('keyword');
        $cars = $this->carModel->search($keyword);

        foreach ($cars as &$car) {
            $car['images'] = $this->carImageModel->forCar((int) $car['CarID'], 4);
        }
        unset($car);

        $this->view('admin/cars/list', [
            'pageTitle' => 'Danh sách xe',
            'cars' => $cars,
            'keyword' => $keyword,
        ]);
    }

    public function create(): void
    {
        $this->view('admin/cars/add', [
            'pageTitle' => 'Thêm xe',
            'brands' => $this->brandModel->all('BrandName ASC'),
            'types' => $this->carTypeModel->all('TypeName ASC'),
        ]);
    }

    public function store(): void
    {
        $data = $this->collectCarInput();

        if ($data['CarName'] === '' || $data['BrandID'] <= 0 || $data['TypeID'] <= 0) {
            die('Dữ liệu xe không hợp lệ.');
        }

        $carId = $this->carModel->create([
            'CarName' => $data['CarName'],
            'BrandID' => $data['BrandID'],
            'TypeID' => $data['TypeID'],
            'Year' => $data['Year'],
            'LicensePlate' => $data['LicensePlate'],
            'Color' => $data['Color'],
            'Seats' => $data['Seats'],
            'Transmission' => $data['Transmission'],
            'FuelType' => $data['FuelType'],
            'PricePerDay' => $data['PricePerDay'],
            'DepositAmount' => $data['DepositAmount'],
            'Status' => $data['Status'],
            'Description' => $data['Description'],
            'MainImage' => '',
            'FolderName' => '',
            'Location' => $data['Location'],
            'Mileage' => $data['Mileage'],
        ]);

        $folderName = 'car_' . $carId;
        $carFolderPath = $this->carsUploadDir() . $folderName;
        if (!is_dir($carFolderPath) && !mkdir($carFolderPath, 0777, true)) {
            die('Không thể tạo thư mục ảnh cho xe.');
        }
        $this->carModel->setFolderName($carId, $folderName);

        $this->uploadNewImages($carId, $folderName, $carFolderPath, false);

        $this->redirect('/Carrental/admin/cars');
    }

    public function edit(string $id): void
    {
        $carId = (int) $id;
        $car = $this->carModel->find($carId);
        if (!$car) {
            $this->redirect('/Carrental/admin/cars');
        }

        $this->view('admin/cars/edit', [
            'pageTitle' => 'Sửa xe',
            'car' => $car,
            'images' => $this->carImageModel->forCar($carId),
            'brands' => $this->brandModel->all('BrandName ASC'),
            'types' => $this->carTypeModel->all('TypeName ASC'),
        ]);
    }

    public function update(string $id): void
    {
        $carId = (int) $id;
        $car = $this->carModel->find($carId);
        if (!$car) {
            die('Không tìm thấy xe.');
        }

        $data = $this->collectCarInput();
        if ($data['CarName'] === '' || $data['BrandID'] <= 0 || $data['TypeID'] <= 0) {
            die('Dữ liệu cập nhật không hợp lệ.');
        }

        $this->carModel->update($carId, [
            'CarName' => $data['CarName'],
            'BrandID' => $data['BrandID'],
            'TypeID' => $data['TypeID'],
            'Year' => $data['Year'],
            'LicensePlate' => $data['LicensePlate'],
            'Color' => $data['Color'],
            'Seats' => $data['Seats'],
            'Transmission' => $data['Transmission'],
            'FuelType' => $data['FuelType'],
            'PricePerDay' => $data['PricePerDay'],
            'DepositAmount' => $data['DepositAmount'],
            'Status' => $data['Status'],
            'Description' => $data['Description'],
            'Location' => $data['Location'],
            'Mileage' => $data['Mileage'],
        ]);

        $folderName = trim($car['FolderName'] ?? '');
        if ($folderName === '') {
            $folderName = 'car_' . $carId;
            $this->carModel->setFolderName($carId, $folderName);
        }

        $carFolderPath = $this->carsUploadDir() . $folderName;
        if (!is_dir($carFolderPath)) {
            mkdir($carFolderPath, 0777, true);
        }

        $this->uploadNewImages($carId, $folderName, $carFolderPath, !empty($car['MainImage']));

        $this->redirect('/Carrental/admin/cars');
    }

    public function destroy(string $id): void
    {
        $carId = (int) $id;
        $car = $this->carModel->find($carId);

        if ($car && !empty($car['FolderName'])) {
            $this->deleteFolderRecursive($this->carsUploadDir() . $car['FolderName']);
        }

        $this->carModel->delete($carId);
        $this->redirect('/Carrental/admin/cars');
    }

    public function updateImage(string $id): void
    {
        $carId = (int) $id;
        $imageId = $this->inputInt('ImageID');
        $imageType = $this->input('ImageType', 'gallery');

        if ($imageId <= 0 || $carId <= 0 || !in_array($imageType, self::ALLOWED_IMAGE_TYPES, true)) {
            die('Dữ liệu ảnh không hợp lệ.');
        }

        $this->carImageModel->updateType($imageId, $carId, $imageType);
        $this->redirect('/Carrental/admin/cars/' . $carId . '/edit');
    }

    public function setMainImage(string $id): void
    {
        $carId = (int) $id;
        $imageId = $this->inputInt('ImageID');

        $image = $this->carImageModel->findForCar($imageId, $carId);
        if (!$image) {
            die('Không tìm thấy ảnh.');
        }

        $this->carImageModel->clearMain($carId);
        $this->carImageModel->setMain($imageId, $carId);
        $this->carModel->setMainImage($carId, $image['ImageURL']);

        $this->redirect('/Carrental/admin/cars/' . $carId . '/edit');
    }

    public function deleteImage(string $id): void
    {
        $carId = (int) $id;
        $imageId = $this->inputInt('ImageID');

        $image = $this->carImageModel->findForCar($imageId, $carId);
        if (!$image) {
            die('Không tìm thấy ảnh.');
        }

        $this->carImageModel->deleteForCar($imageId, $carId);

        if ((int) $image['IsMain'] === 1) {
            $next = $this->carImageModel->oldestForCar($carId);
            if ($next) {
                $this->carImageModel->setMain((int) $next['ImageID'], $carId);
                $this->carModel->setMainImage($carId, $next['ImageURL']);
            } else {
                $this->carModel->setMainImage($carId, '');
            }
        }

        $baseDir = realpath($this->carsUploadDir());
        $imagePath = $baseDir
            ? realpath($baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $image['ImageURL']))
            : false;

        if ($baseDir && $imagePath && strpos($imagePath, $baseDir . DIRECTORY_SEPARATOR) === 0 && is_file($imagePath)) {
            unlink($imagePath);
        }

        $this->redirect('/Carrental/admin/cars/' . $carId . '/edit');
    }

    /**
     * Doc toan bo field dung chung giua store() va update() tu $_POST.
     */
    private function collectCarInput(): array
    {
        return [
            'CarName' => $this->input('CarName'),
            'BrandID' => $this->inputInt('BrandID'),
            'TypeID' => $this->inputInt('TypeID'),
            'Year' => $this->inputInt('Year'),
            'LicensePlate' => $this->input('LicensePlate'),
            'Color' => $this->input('Color'),
            'Seats' => $this->inputInt('Seats'),
            'Transmission' => $this->input('Transmission'),
            'FuelType' => $this->input('FuelType'),
            'PricePerDay' => $this->inputFloat('PricePerDay'),
            'DepositAmount' => $this->inputFloat('DepositAmount'),
            'Status' => $this->input('Status', 'Available'),
            'Description' => $this->input('Description'),
            'Location' => $this->input('Location'),
            'Mileage' => $this->inputInt('Mileage'),
        ];
    }

    /**
     * Upload cac anh moi tu $_FILES['Images'] (input nhieu file), luu vao
     * CarImages + gan anh chinh dau tien neu xe chua co anh chinh.
     */
    private function uploadNewImages(int $carId, string $folderName, string $carFolderPath, bool $hasMain): void
    {
        if (empty($_FILES['Images']['name'][0])) {
            return;
        }

        foreach ($_FILES['Images']['name'] as $index => $originalName) {
            if ($_FILES['Images']['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $tmpName = $_FILES['Images']['tmp_name'][$index];
            if (!isRealImageUpload($tmpName, $ext, self::ALLOWED_EXT)) {
                continue;
            }

            $newFileName = moveValidatedUpload($tmpName, $carFolderPath, 'img_' . ($index + 1), $ext);
            if ($newFileName === false) {
                continue;
            }

            $imageUrl = $folderName . '/' . $newFileName;
            $isMain = $hasMain ? 0 : 1;

            $this->carImageModel->create([
                'CarID' => $carId,
                'ImageURL' => $imageUrl,
                'IsMain' => $isMain,
                'ImageType' => 'gallery',
            ]);

            if (!$hasMain) {
                $this->carModel->setMainImage($carId, $imageUrl);
                $hasMain = true;
            }
        }
    }

    private function carsUploadDir(): string
    {
        return __DIR__ . '/../../../public/frontend/assets/img/cars/';
    }

    private function deleteFolderRecursive(string $folderPath): void
    {
        if (!is_dir($folderPath)) {
            return;
        }

        foreach (array_diff(scandir($folderPath), ['.', '..']) as $item) {
            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                $this->deleteFolderRecursive($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($folderPath);
    }
}
