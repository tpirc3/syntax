<?php

/**
 * LR parser generated by the Syntax tool.
 *
 * https://www.npmjs.com/package/syntax-cli
 *
 *   npm install -g syntax-cli
 *
 *   syntax-cli --help
 *
 * To regenerate run:
 *
 *   syntax-cli \
 *     --grammar ~/path-to-grammar-file \
 *     --mode <parsing-mode> \
 *     --output ~/ParserClassName.php
 */

<<NAMESPACE>>

class SyntaxException extends \Exception {}

<<MODULE_INCLUDE>>

/**
 * Base class for all generated LR parsers.
 */
class yyparse {

  /**
   * Productions table (generated by Syntax tool).
   *
   * Format of a row:
   *
   * [ <NonTerminal Index>, <RHS.length>, <semanticActionName> ]
   */
  private static $productions = <<PRODUCTIONS>>;

  /**
   * Tokens map (from token type to encoded index, autogenerated).
   */
  private static $tokens = <<TOKENS>>;

  /**
   * Parsing table (generated by Syntax tool).
   */
  private static $table = <<TABLE>>;

  /**
   * Parsing stack.
   */
  private static $stack = [];

  /**
   * Result of a semantic action (used as `$$`).
   */
  private static $__ = null;

  /**
   * Result location (used as `@$`).
   */
  private static $__loc = null;

  /**
   * Parser event callbacks.
   */
  private static $on_parse_begin = null;
  private static $on_parse_end = null;

  /**
   * Matched token text.
   */
  public static $yytext = '';

  /**
   * Matched token length.
   */
  public static $yyleng = 0;

  /**
   * End of file symbol.
   */
  const EOF = '$';

  /**
   * Tokenizer instance.
   */
  private static $tokenizer = null;

  <<PRODUCTION_HANDLERS>>

  private static $shouldCaptureLocations = <<CAPTURE_LOCATIONS>>;

  private static function yyloc($start, $end) {
    // Epsilon doesn't produce location.
    if (!$start || !$end) {
      return !$start ? $end : $static;
    }

    return array(
      'startOffset' => $start['startOffset'],
      'endOffset' => $end['endOffset'],
      'startLine' => $start['startLine'],
      'endLine' => $end['endLine'],
      'startColumn' => $start['startColumn'],
      'endColumn' => $end['endColumn'],
    );
  }

  public static function setTokenizer($tokenizer) {
    self::$tokenizer = $tokenizer;
  }

  public static function getTokenizer() {
    return self::$tokenizer;
  }

  public static function setOnParseBegin($on_parse_begin) {
    self::$on_parse_begin = $on_parse_begin;
  }

  public static function setOnParseEnd($on_parse_end) {
    self::$on_parse_end = $on_parse_end;
  }

  public static function parse($string) {
    if (self::$on_parse_begin) {
      $on_parse_begin = self::$on_parse_begin;
      $on_parse_begin($string);
    }

    $tokenizer = self::getTokenizer();

    if (!$tokenizer) {
      throw new SyntaxException(`Tokenizer is not provided.`);
    }

    $tokenizer->initString($string);

    $stack = &self::$stack;
    $stack = ['0'];

    $tokens = &self::$tokens;
    $table = &self::$table;
    $productions = &self::$productions;

    $token = $tokenizer->getNextToken();
    $shifted_token = null;

    do {
      if (!$token) {
        self::unexpectedEndOfInput();
      }

      $state = end($stack);
      $column = $tokens[$token['type']];

      if (!isset($table[$state][$column])) {
        self::unexpectedToken($token);
      }
      $entry = $table[$state][$column];

      if ($entry[0] === 's') {
        $loc = null;

        if (self::$shouldCaptureLocations) {
          $loc = array(
            'startOffset' => $token['startOffset'],
            'endOffset'=> $token['endOffset'],
            'startLine' => $token['startLine'],
            'endLine' => $token['endLine'],
            'startColumn' => $token['startColumn'],
            'endColumn' => $token['endColumn'],
          );
        }

        array_push(
          $stack,
          array(
            'symbol' => $tokens[$token['type']],
            'semanticValue' => $token['value'],
            'loc' => $loc,
          ),
          intval(substr($entry, 1))
        );
        $shifted_token = $token;
        $token = $tokenizer->getNextToken();
      } else if ($entry[0] === 'r') {
        $production_number = intval(substr($entry, 1));
        $production = $productions[$production_number];
        $has_semantic_action = count($production) > 2;
        $semantic_value_args = $has_semantic_action ? [] : null;

        $location_args = (
          $has_semantic_action && self::$shouldCaptureLocations
            ? []
            : null
        );

        if ($production[1] !== 0) {
          $rhs_length = $production[1];
          while ($rhs_length-- > 0) {
            array_pop($stack);
            $stack_entry = array_pop($stack);

            if ($has_semantic_action) {
              array_unshift(
                $semantic_value_args,
                $stack_entry['semanticValue']
              );

              if ($location_args !== null) {
                array_unshift(
                  $location_args,
                  $stack_entry['loc']
                );
              }
            }
          }
        }

        $reduce_stack_entry = array('symbol' => $production[0]);

        if ($has_semantic_action) {
          self::$yytext = $shifted_token ? $shifted_token['value'] : null;
          self::$yyleng = $shifted_token ? strlen($shifted_token['value']) : null;

          forward_static_call_array(
            array('self', $production[2]),
            $location_args !== null
              ? array_merge($semantic_value_args, $location_args)
              : $semantic_value_args
          );

          $reduce_stack_entry['semanticValue'] = self::$__;

          if ($location_args !== null) {
            $reduce_stack_entry['loc'] = self::$__loc;
          }
        }

        $next_state = end($stack);
        $symbol_to_reduce_with = $production[0];

        array_push(
          $stack,
          $reduce_stack_entry,
          $table[$next_state][$symbol_to_reduce_with]
        );
      } else if ($entry === 'acc') {
        array_pop($stack);
        $parsed = array_pop($stack);

        if (count($stack) !== 1 ||
            $stack[0] !== '0' ||
            $tokenizer->hasMoreTokens()) {
          self::unexpectedToken($token);
        }

        $parsed_value = array_key_exists('semanticValue', $parsed)
          ? $parsed['semanticValue']
          : true;

        if (self::$on_parse_end) {
          $on_parse_end = self::$on_parse_end;
          $on_parse_end($parsed_value);
        }

        return $parsed_value;
      }

    } while ($tokenizer->hasMoreTokens() || count($stack) > 1);
  }

  private static function unexpectedToken($token) {
    if ($token['value'] === self::EOF) {
      self::unexpectedEndOfInput();
    }

    self::getTokenizer()->throwUnexpectedToken(
      $token['value'],
      $token['startLine'],
      $token['startColumn']
    );
  }

  private static function unexpectedEndOfInput() {
    self::parseError('Unexpected end of input.');
  }

  private static function parseError($message) {
    throw new SyntaxException('SyntaxError: ' . $message);
  }
}

<<TOKENIZER>>

class <<PARSER_CLASS_NAME>> extends yyparse {}
