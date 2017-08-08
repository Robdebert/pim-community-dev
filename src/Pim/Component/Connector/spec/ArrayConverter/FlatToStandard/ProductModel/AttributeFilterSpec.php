<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel\AttributeFilter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeFilterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith($familyVariantRepository, $productModelRepository, ['code', 'parent', 'family_variant']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeFilter::class);
    }

    function it_filters_the_attributes_for_a_root_product_model(
        $familyVariantRepository,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributeCollection
    ) {
        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributeCollection);
        $commonAttributeCollection->exists(Argument::any())->willReturn(true, false);

        $this->filter([
            'code' => 'code',
            'parent' => '',
            'family_variant' => 'family_variant',
            'values' => [
                'name-en_US' => 'name',
                'description-en_US-ecommerce' => 'description',
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => '',
            'family_variant' => 'family_variant',
            'values' => [
                'name-en_US' => 'name',
            ]
        ]);
    }

    function it_filters_the_attributes_for_a_sub_product_model(
        $familyVariantRepository,
        $productModelRepository,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $productModel,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $familyVariantAttribute
    ) {
        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $productModelRepository->findOneByIdentifier('parent')->willreturn($productModel);
        $productModel->getVariationLevel()->willReturn(1);
        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($familyVariantAttribute);
        $familyVariantAttribute->exists(Argument::any())->willReturn(false, true);

        $this->filter([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [
                'name-en_US' => 'name',
                'description-en_US-ecommerce' => 'description',
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [
                'description-en_US-ecommerce' => 'description',
            ]
        ]);
    }

    function it_throws_an_exception_if_the_family_variant()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('filter', [
            'parent' => 'parent',
        ]);
    }
}
