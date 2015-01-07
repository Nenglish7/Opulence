<?php
/**
 * Copyright (C) 2015 David Young
 * 
 * Defines the string parser
 */
namespace RDev\Console\Requests\Parsers;
use RDev\Console\Requests;

class String implements IParser
{
    /**
     * {@inheritdoc}
     */
    public function parse($input)
    {
        $request = new Requests\Request();
        $tokens = $this->tokenize($input);
        $argumentCounter = 0;

        while($token = array_shift($tokens))
        {
            if(substr($token, 0, 2) == "--")
            {
                if(strpos($token, "=") === false)
                {
                    // It's of the form "--foo bar"
                    $nextToken = array_shift($tokens);
                    // Make it "--foo=bar"
                    $token .= "=" . $nextToken;
                }

                $option = $this->parseLongOption(substr($token, 2));
                $request->addOptionValue($option[0], $option[1]);
            }
            elseif(substr($token, 0, 1) == "-")
            {
                $options = $this->parseShortOption(substr($token, 1));

                foreach($options as $option)
                {
                    $request->addOptionValue($option[0], $option[1]);
                }
            }
            else
            {
                if($argumentCounter == 0)
                {
                    // We consider this to be the command name
                    $request->setCommandName($token);
                }
                else
                {
                    // We consider this to be an argument
                    $token = $this->trimQuotes($token);
                    $request->addArgumentValue($token);
                }

                $argumentCounter++;
            }
        }

        return $request;
    }

    /**
     * Parses a long option token and returns an array of data
     *
     * @param string $token The token to parse
     * @return array The name of the option mapped to its value
     * @throws \RuntimeException Thrown if the option could not be parsed
     */
    private function parseLongOption($token)
    {
        list($name, $value) = explode("=", $token);
        $value = $this->trimQuotes($value);

        return [$name, $value];
    }

    /**
     * Parses a short option token and returns an array of data
     *
     * @param string $token The token to parse
     * @return array The name of the option mapped to its value
     * @throws \RuntimeException Thrown if the option could not be parsed
     */
    private function parseShortOption($token)
    {
        $options = [];

        // Each character in a short option is an option
        for($charIter = 0;$charIter < strlen($token);$charIter++)
        {
            $options[] = [$token[$charIter], null];
        }

        return $options;
    }

    /**
     * Tokenizes a request string
     *
     * @param string $input The input to tokenize
     * @return array The list of tokens
     */
    private function tokenize($input)
    {
        $input = trim($input);
        $inDoubleQuotes = false;
        $inSingleQuotes = false;
        $inputLength = strlen($input);
        $previousChar = "";
        $buffer = "";
        $tokens = [];

        for($charIter = 0;$charIter < $inputLength;$charIter++)
        {
            $char = $input[$charIter];

            switch($char)
            {
                case '"':
                    // If the double quote is inside single quotes, we treat it as part of a quoted string
                    if(!$inSingleQuotes)
                    {
                        $inDoubleQuotes = !$inDoubleQuotes;
                    }

                    $buffer .= '"';

                    break;
                case "'":
                    // If the single quote is inside double quotes, we treat it as part of a quoted string
                    if(!$inDoubleQuotes)
                    {
                        $inSingleQuotes = !$inSingleQuotes;
                    }

                    $buffer .= "'";

                    break;
                default:
                    if($inDoubleQuotes || $inSingleQuotes || $char != " ")
                    {
                        $buffer .= $char;
                    }
                    elseif($char == " " && $previousChar != " " && strlen($buffer) > 0)
                    {
                        // We've hit a space outside a quoted string, so flush the buffer
                        $tokens[] = $buffer;
                        $buffer = "";
                    }
            }

            $previousChar = $char;
        }

        // Flush out the buffer
        if(strlen($buffer) > 0)
        {
            $tokens[] = $buffer;
        }

        if($inDoubleQuotes || $inSingleQuotes)
        {
            throw new \RuntimeException("Unclosed " . ($inDoubleQuotes ? "double" : "single") . " quote");
        }

        return $tokens;
    }

    /**
     * Trims the outer-most quotes from a token
     *
     * @param string $token Trims quotes off of a token
     * @return string The trimmed token
     */
    private function trimQuotes($token)
    {
        // Trim any quotes
        if(($firstValueChar = substr($token, 0, 1)) == substr($token, -1))
        {
            if($firstValueChar == "'")
            {
                $token = trim($token, "'");
            }
            elseif($firstValueChar == '"')
            {
                $token = trim($token, '"');
            }
        }

        return $token;
    }
}