<?php

/*
 * This file is part of the Idlab Helper.
 *
 * (c) Idlab - Michael Vetterli (michael@idlab.ch)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$fileHeaderComment = <<<'EOF'
This file is part of the Idlab Helper.

(c) Idlab - Michael Vetterli (michael@idlab.ch)

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'native_constant_invocation' => ['strict' => false],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'header_comment' => ['header' => $fileHeaderComment],
        'modernize_strpos' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__.'/src')
            ->append([__FILE__])
    )
;
