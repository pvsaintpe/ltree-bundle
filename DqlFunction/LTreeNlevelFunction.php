<?php

namespace LTree\DqlFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\AST\ASTException;

/**
 * Class LTreeNlevelFunction
 * @package LTree\DqlFunction
 */
class LTreeNlevelFunction extends FunctionNode
{
    public const FUNCTION_NAME = 'ltree_nlevel';

    /**
     * @var Node
     */
    protected $tree;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('nlevel(%s)', $this->tree->dispatch($sqlWalker));
    }

    /**
     * @param Parser $parser
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->tree = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
