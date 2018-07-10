<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * @Annotation
 */
class EthereumAddress extends Regex
{
    public function __construct(?array $options = null)
    {
        $options['pattern'] = '/^0x[a-fA-Z0-9]{40}$/';
        $options['message'] = 'Invalid ethereum address';

        parent::__construct($options);
    }
}
