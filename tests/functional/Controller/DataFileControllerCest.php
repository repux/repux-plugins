<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\test\UserFixture;
use App\Entity\DataFile;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class DataFileControllerCest
{
    const BASE_PATH = '/api/data-file';
    const DOWNLOAD_PATH = '/api/data-file/download';

    public function _before(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->amTokenAuthenticated(UserFixture::FIRST_USER_ADDRESS);
    }

    public function getOne(\FunctionalTester $I)
    {
        $dataFile = $this->haveDataFileInRepository($I, UserFixture::FIRST_USER_ADDRESS);

        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, $dataFile->getId()));
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'data_file' => [
                'id' => $dataFile->getId(),
                'origin' => $dataFile->getOrigin(),
                'file_mime_type' => $dataFile->getFileMimeType(),
                'file_size' => $dataFile->getFileSize(),
                'original_name' => $dataFile->getOriginalName(),
            ]
        ]);
    }

    public function getOneFromOtherUser(\FunctionalTester $I)
    {
        $dataFile = $this->haveDataFileInRepository($I, UserFixture::SECOND_USER_ADDRESS);

        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, $dataFile->getId()));
        $I->seeResponseCodeIs(Response::HTTP_NOT_FOUND);
    }

    public function getOneNotAuthenticated(\FunctionalTester $I)
    {
        $I->amNotTokenAuthenticated();
        $dataFile = $this->haveDataFileInRepository($I, UserFixture::FIRST_USER_ADDRESS);

        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, $dataFile->getId()));
        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
    }

    public function getOneNonExisting(\FunctionalTester $I)
    {
        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, 123));
        $I->seeResponseCodeIs(Response::HTTP_NOT_FOUND);
    }

    public function download(\FunctionalTester $I)
    {
        $dataFile = $this->haveDataFileInRepository($I, UserFixture::FIRST_USER_ADDRESS);
        $filePath = sprintf(codecept_data_dir('storage/%s'), $dataFile->getFileId());

        $I->sendGET(sprintf('%s/%s', self::DOWNLOAD_PATH, $dataFile->getId()));
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->assertEquals(file_get_contents($filePath), $I->grabResponse());
    }

    public function downloadFromOtherUser(\FunctionalTester $I)
    {
        $dataFile = $this->haveDataFileInRepository($I, UserFixture::SECOND_USER_ADDRESS);
        $filePath = sprintf(codecept_data_dir('storage/%s'), $dataFile->getFileId());

        $I->sendGET(sprintf('%s/%s', self::DOWNLOAD_PATH, $dataFile->getId()));
        $I->seeResponseCodeIs(Response::HTTP_NOT_FOUND);
        $I->assertNotEquals(file_get_contents($filePath), $I->grabResponse());
    }

    private function haveDataFileInRepository(\FunctionalTester $I, string $userAddress): DataFile
    {
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => $userAddress]);

        $id = $I->haveInRepository(DataFile::class, [
            'user' => $user,
            'origin' => DataFile::ORIGIN_SHOPIFY,
            'fileMimeType' => 'text/something',
            'fileSize' => 123,
            'originalName' => 'original-name.file',
            'fileId' => 'sample.txt',
        ]);

        return $I->grabEntityFromRepository(DataFile::class, ['id' => $id]);
    }
}
