<?php

namespace App\Services;

use InvalidArgumentException;

class BookQueryParser
{
    private array $tokens = [];

    private int $position = 0;

    public function parse(string $query)
    {
        $this->tokens = $this->tokens($query);
        $this->position = 0;

        if (count($this->tokens) === 0) {
            throw new InvalidArgumentException('Invalid query');
        }

        $ast = $this->parseOr();

        if ($this->current() !== null) {
            throw new InvalidArgumentException('Invalid query');
        }

        return $ast;
    }

    private function parseOr()
    {
        $left = $this->parseAnd();

        while ($this->current() === 'OR') {
            $this->position++;
            $right = $this->parseAnd();
            $left = [
                'type' => 'OR',
                'left' => $left,
                'right' => $right,
            ];
        }

        return $left;
    }

    private function parseAnd()
    {
        $left = $this->parsePrimary();

        while ($this->current() === 'AND') {
            $this->position++;
            $right = $this->parsePrimary();
            $left = [
                'type' => 'AND',
                'left' => $left,
                'right' => $right,
            ];
        }

        return $left;
    }

    private function parsePrimary()
    {
        $token = $this->current();

        if ($token === null || $token === 'AND' || $token === 'OR' || $token === ')') {
            throw new InvalidArgumentException('Invalid query');
        }

        if ($token === '(') {
            $this->position++;
            $node = $this->parseOr();

            if ($this->current() !== ')') {
                throw new InvalidArgumentException('Invalid query');
            }

            $this->position++;

            return $node;
        }

        $this->position++;

        return [
            'type' => 'TERM',
            'value' => $token,
        ];
    }

    private function tokens(string $query)
    {
        $tokens = [];
        $term = '';
        $length = strlen($query);

        for ($index = 0; $index < $length; $index++) {
            $char = $query[$index];

            if ($char === '(' || $char === ')') {
                $this->pushTerm($tokens, $term);
                $term = '';
                $tokens[] = $char;
                continue;
            }

            if ($this->isOperatorAt($query, $index, 'AND')) {
                $this->pushTerm($tokens, $term);
                $term = '';
                $tokens[] = 'AND';
                $index += 2;
                continue;
            }

            if ($this->isOperatorAt($query, $index, 'OR')) {
                $this->pushTerm($tokens, $term);
                $term = '';
                $tokens[] = 'OR';
                $index += 1;
                continue;
            }

            $term .= $char;
        }

        $this->pushTerm($tokens, $term);

        return $tokens;
    }

    private function pushTerm(array &$tokens, string $term): void
    {
        $term = trim($term);

        if ($term !== '') {
            $tokens[] = $term;
        }
    }

    private function isOperatorAt(string $query, int $index, string $operator)
    {
        $length = strlen($operator);

        if (substr($query, $index, $length) !== $operator) {
            return false;
        }

        $before = $index === 0 ? ' ' : $query[$index - 1];
        $afterIndex = $index + $length;
        $after = $afterIndex >= strlen($query) ? ' ' : $query[$afterIndex];

        return $this->isBoundary($before) && $this->isBoundary($after);
    }

    private function isBoundary(string $char)
    {
        return $char === ' ' || $char === '(' || $char === ')';
    }

    private function current()
    {
        return $this->tokens[$this->position] ?? null;
    }
}
