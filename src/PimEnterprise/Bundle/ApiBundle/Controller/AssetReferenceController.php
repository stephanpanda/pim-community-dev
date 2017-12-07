<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetReferenceController
{
    const NON_LOCALIZABLE_REFERENCE = 'no_locale';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param FilesystemProvider                    $filesystemProvider
     * @param FileFetcherInterface                  $fileFetcher
     * @param NormalizerInterface                   $normalizer
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        NormalizerInterface $normalizer
    ) {
        $this->assetRepository = $assetRepository;
        $this->localeRepository = $localeRepository;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
        $this->normalizer = $normalizer;
    }

    /**
     * @param string $assetCode
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_reference_list")
     */
    public function downloadAction(string $assetCode, string $localeCode): Response
    {
        $locale = $this->getLocale($localeCode);
        $referenceFile = $this->getReference($assetCode, $locale)->getFileInfo();

        $fs = $this->filesystemProvider->getFilesystem(FileStorage::ASSET_STORAGE_ALIAS);
        $options = [
            'headers' => [
                'Content-Type'        => $referenceFile->getMimeType(),
                'Content-Disposition' => sprintf('attachment; filename="%s"', $referenceFile->getOriginalFilename())
            ]
        ];

        try {
            return $this->fileFetcher->fetch($fs, $referenceFile->getKey(), $options);
        } catch (FileTransferException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (FileNotFoundException $e) {
            $localizableMessage = null !== $locale ? sprintf(' and the locale "%s"', $locale->getCode()) : '';
            $notFoundMessage = sprintf(
                'Reference file for the asset "%s"%s does not exist.',
                $assetCode,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage, $e);
        }
    }

    /**
     * @param string $assetCode
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_reference_list")
     */
    public function getAction(string $assetCode, string $localeCode): Response
    {
        $locale = $this->getLocale($localeCode);
        $reference = $this->getReference($assetCode, $locale);

        $normalizedReference = $this->normalizer->normalize($reference, 'external_api');

        return new JsonResponse($normalizedReference);
    }

    /**
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     *
     * @return null|LocaleInterface
     */
    protected function getLocale(string $localeCode): ?LocaleInterface
    {
        if (static::NON_LOCALIZABLE_REFERENCE === $localeCode) {
            return null;
        }

        $locale = $this->localeRepository->findOneByIdentifier($localeCode);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist.', $localeCode));
        }

        return $locale;
    }

    /**
     * @param string               $assetCode
     * @param null|LocaleInterface $locale
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return ReferenceInterface
     */
    protected function getReference(string $assetCode, ?LocaleInterface $locale): ReferenceInterface
    {
        $asset = $this->getAsset($assetCode);

        if ($asset->isLocalizable() && null === $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is localizable, you must provide a locale and the locale "no_locale" does not exist.',
                $asset->getCode()
            ));
        }

        if (!$asset->isLocalizable() && null !== $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is not localizable, you must provide the string "no_locale" as a locale.',
                $asset->getCode()
            ));
        }

        $reference = $asset->getReference($locale);

        $localizableMessage = null !== $locale ? sprintf(' and the locale "%s"', $locale->getCode()) : '';
        $notFoundMessage = sprintf(
            'Reference file for the asset "%s"%s does not exist.',
            $asset->getCode(),
            $localizableMessage
        );

        if (null === $reference || null === $reference->getFileInfo()) {
            throw new NotFoundHttpException($notFoundMessage);
        }

        return $reference;
    }

    /**
     * @param string $assetCode
     *
     * @throws NotFoundHttpException
     *
     * @return AssetInterface
     */
    protected function getAsset(string $assetCode): AssetInterface
    {
        $asset = $this->assetRepository->findOneByIdentifier($assetCode);
        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset "%s" does not exist.', $assetCode));
        }

        return $asset;
    }
}