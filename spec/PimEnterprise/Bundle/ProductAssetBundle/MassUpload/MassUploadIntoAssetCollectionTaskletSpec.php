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

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\MassUpload;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\ProductAssetBundle\MassUpload\MassUploadIntoAssetCollectionTasklet;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\EntityToAddAssetsInto;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadIntoAssetCollectionProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoAssetCollectionTaskletSpec extends ObjectBehavior
{
    function let(
        MassUploadIntoAssetCollectionProcessor $massUploadToProductProcessor,
        MassUploadIntoAssetCollectionProcessor $massUploadToProductModelProcessor,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $massUploadToProductProcessor,
            $massUploadToProductModelProcessor,
            '/tmp/pim/file_storage'
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_mass_upload_to_product_tasklet()
    {
        $this->shouldHaveType(MassUploadIntoAssetCollectionTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_mass_upload_files_into_a_product(
        $stepExecution,
        $massUploadToProductProcessor,
        $massUploadToProductModelProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_a.jpg'),
            ProcessedItem::STATE_SUCCESS,
            'Reason for success'
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto(42, 'asset_collection')
        )->willReturn($processedItemList);
        $massUploadToProductModelProcessor->applyMassUpload(Argument::cetera())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalled();

        $this->execute();
    }

    function it_mass_upload_files_into_a_product_model(
        $stepExecution,
        $massUploadToProductProcessor,
        $massUploadToProductModelProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_a.jpg'),
            ProcessedItem::STATE_SUCCESS,
            'Reason for success'
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product-model');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->applyMassUpload(Argument::cetera())->shouldNotBeCalled();
        $massUploadToProductModelProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto(42, 'asset_collection')
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalled();

        $this->execute();
    }

    function it_skips_files_during_mass_upload(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_b.jpg'),
            ProcessedItem::STATE_SKIPPED,
            'Reason to be skipped'
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto(42, 'asset_collection')
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('variations_not_generated')->shouldBeCalled();
        $stepExecution->addWarning(
            'Reason to be skipped',
            [],
            new DataInvalidItem(['filename' => 'file_b.jpg'])
        )->shouldBeCalled();

        $this->execute();
    }

    function it_stops_the_mass_upload_in_case_of_errors_on_asset_generation(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_c.jpg'),
            ProcessedItem::STATE_ERROR,
            '',
            new \Exception('Exception message')
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto(42, 'asset_collection')
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalled();
        $stepExecution->addError('Exception message')->shouldBeCalled();

        $this->execute();
    }

    function it_stops_the_mass_upload_in_case_of_errors_on_entity_with_values_validation(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new EntityToAddAssetsInto(42, 'asset_collection'),
            ProcessedItem::STATE_ERROR,
            '',
            new \Exception('Exception message')
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto(42, 'asset_collection')
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalled();
        $stepExecution->addError('Exception message')->shouldBeCalled();

        $this->execute();
    }

    function it_throws_an_exception_if_processed_item_is_not_a_file(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(new \StdClass(), ProcessedItem::STATE_SUCCESS, 'Reason for success');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto(42, 'asset_collection')
        )->willReturn($processedItemList);

        $this->shouldThrow(\InvalidArgumentException::class)->during('execute');
    }
}
