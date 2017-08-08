<?php
declare(strict_types=1);

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeFilter
{
    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyVariantRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * @param $flatProductModel
     *
     * @return array
     */
    public function filter($flatProductModel): array
    {
        if (!isset($flatProductModel['family_variant'])) {
            throw new \InvalidArgumentException('The product model family variant must be provided');
        }

        /** @var FamilyVariantInterface $familyVariant */
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($flatProductModel['family_variant']);
        $parent = $flatProductModel['parent'] ?? '';
        if (empty($parent)) {
            return $this->removeUnknownAttributes($flatProductModel, $familyVariant->getCommonAttributes());
        }

        /** @var ProductModelInterface $parentProductModel */
        $parentProductModel = $this->productModelRepository->findOneByIdentifier($flatProductModel['parent']);
        $variantAttributeSet = $familyVariant->getVariantAttributeSet($parentProductModel->getVariationLevel()+1);

        return $this->removeUnknownAttributes($flatProductModel, $variantAttributeSet->getAttributes());
    }

    /**
     * @param array      $flatProductModel
     * @param Collection $familyVariantAttribute
     *
     * @return array
     */
    private function removeUnknownAttributes(array $flatProductModel, Collection $familyVariantAttribute): array
    {
        foreach ($flatProductModel['values'] as $attributeName => $value) {
            $shortAttributeName = explode('-', $attributeName);
            $shortAttributeName = $shortAttributeName[0];

            $belongToFamilyVariant = $familyVariantAttribute->exists(
                function ($key, AttributeInterface $attribute) use ($shortAttributeName) {
                    return $attribute->getCode() === $shortAttributeName;
                }
            );

            if (!$belongToFamilyVariant) {
                unset($flatProductModel['values'][$attributeName]);
            }
        }

        return $flatProductModel;
    }
}
