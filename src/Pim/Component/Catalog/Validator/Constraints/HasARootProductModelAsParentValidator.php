<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validate that a sub product model can only have a root product model as parent
 * 
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasARootProductModelAsParentValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @param $productModel ProductModelInterface
     */
    public function validate($productModel, Constraint $constraint): void
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw new UnexpectedTypeException($productModel, ProductModelInterface::class);
        }

        if (!$constraint instanceof HasARootProductModelAsParent) {
            throw new UnexpectedTypeException($constraint, HasARootProductModelAsParent::class);
        }

        if ($productModel->isRootProductModel()) {
            return;
        }

        if (false === $productModel->getParent()->isRootProductModel()) {
            $this->context->buildViolation(HasARootProductModelAsParent::INVALID_PARENT)->addViolation();
        }
    }
}
