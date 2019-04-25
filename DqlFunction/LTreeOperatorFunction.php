<?php

namespace LTree\DqlFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\AST\ASTException;

/**
 * Class LTreeOperatorFunction
 * @package LTree\DqlFunction
 */
class LTreeOperatorFunction extends FunctionNode
{
    public const FUNCTION_NAME = 'ltree_operator';

    /**
     * @var Literal
     */
    protected $operator;

    /**
     * @var Node
     */
    protected $first;

    /**
     * @var Node
     */
    protected $second;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('(%s %s %s)',
            $this->first->dispatch($sqlWalker),
            $this->operator->value,
            $this->second->dispatch($sqlWalker)
        );
    }

    /**
     * @param Parser $parser
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->first = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_COMMA);
        $this->operator = $parser->StringExpression();

        $parser->match(Lexer::T_COMMA);
        $this->second = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
