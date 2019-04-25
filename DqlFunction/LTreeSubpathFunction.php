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
 * Class LTreeSubpathFunction
 * @package LTree\DqlFunction
 */
class LTreeSubpathFunction extends FunctionNode
{
    public const FUNCTION_NAME = 'ltree_subpath';

    /**
     * @var Node
     */
    protected $first;

    /**
     * @var Node
     */
    protected $second;

    /**
     * @var Node
     */
    protected $third;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return $this->third === null
            ? sprintf(
                'subpath(%s, %s)',
                $this->first->dispatch($sqlWalker),
                $this->second->dispatch($sqlWalker)
            )
            : sprintf(
                'subpath(%s, %s, %s)',
                $this->first->dispatch($sqlWalker),
                $this->second->dispatch($sqlWalker),
                $this->third->dispatch($sqlWalker)
            )
        ;
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
        $this->second = $parser->ArithmeticPrimary();

        // parse third parameter if available
        if (Lexer::T_COMMA === $parser->getLexer()->lookahead['type']) {
            $parser->match(Lexer::T_COMMA);
            $this->third = $parser->ScalarExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
