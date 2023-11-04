<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/tools/ecs/vendor/contao/easy-coding-standard/config/contao.php']);

    $ecsConfig->skip([
        BlankLineAfterOpeningTagFixer::class,
        DeclareStrictTypesFixer::class,
        HeaderCommentFixer::class,
        LinebreakAfterOpeningTagFixer::class,
        NoAlternativeSyntaxFixer::class,
        ReferenceUsedNamesOnlySniff::class,
        SemicolonAfterInstructionFixer::class,
        StatementIndentationFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        VisibilityRequiredFixer::class,
        VoidReturnFixer::class,
        DisallowArrayTypeHintSyntaxSniff::class => ['*Model.php'],
    ]);

    $ecsConfig->parallel();
    $ecsConfig->lineEnding("\n");
    $ecsConfig->fileExtensions(['html5']);
    $ecsConfig->cacheDirectory(sys_get_temp_dir().'/ecs_template_cache');
};
