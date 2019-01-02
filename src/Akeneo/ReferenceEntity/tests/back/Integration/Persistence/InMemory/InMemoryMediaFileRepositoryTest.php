<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryMediaFileRepository;
use Akeneo\ReferenceEntity\Domain\Repository\MediaFileNotFoundException;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\TestCase;

class InMemoryMediaFileRepositoryTest extends TestCase
{
    /** @var InMemoryMediaFileRepository */
    private $mediaFileRepository;

    public function setUp()
    {
        parent::setUp();

        $this->mediaFileRepository = new InMemoryMediaFileRepository();
    }

    /**
     * @test
     */
    public function it_returns_a_media_file_by_its_identifier()
    {
        $expectedFile = $this->loadMediaFile();

        $mediaFile = $this->mediaFileRepository->getByIdentifier('tests/images/kartell.jpg');

        $this->assertEquals($expectedFile, $mediaFile);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_media_file_was_not_found()
    {
        $this->loadMediaFile();

        $this->expectException(MediaFileNotFoundException::class);
        $this->mediaFileRepository->getByIdentifier('unknown_image.png');
    }

    private function loadMediaFile(): FileInfo
    {
        $mediaFile = new FileInfo();
        $mediaFile->setKey('tests/images/kartell.jpg');
        $mediaFile->setMimeType('image/jpeg');
        $mediaFile->setOriginalFilename('kartell.jpg');
        $mediaFile->setSize(1024);
        $mediaFile->setExtension('jpg');
        $mediaFile->setHash('imagehash');
        $mediaFile->setStorage('catalogStorage');

        $this->mediaFileRepository->save($mediaFile);

        return $mediaFile;
    }
}