<?php

namespace TopdataSoftwareGmbH\Util;

use App\Enum\CodeTypeEnum;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * TODO this gets replaced by UtilSyntaxHighlightingCli
 * 12/2023 created
 */
class UtilSyntaxHighlighting
{
    /**
     * 12/2023 created, used by ai, cm
     *
     * @param string $language
     * @param string $sourceCode
     * @return string
     */
    public static function highlightCodeForCli(string $language, string $sourceCode): string
    {


        //Formatters:
        //~~~~~~~~~~~
        //* bbcode, bb:
        //    Format tokens with BBcodes. These formatting codes are used by many bulletin boards, so you can highlight your sourcecode with pygments before posting it there.
        //* bmp, bitmap:
        //    Create a bitmap image from source code. This uses the Python Imaging Library to generate a pixmap from the source code. (filenames *.bmp)
        //* gif:
        //    Create a GIF image from source code. This uses the Python Imaging Library to generate a pixmap from the source code. (filenames *.gif)
        //* groff, troff, roff:
        //    Format tokens with groff escapes to change their color and font style.
        //* html:
        //    Format tokens as HTML 4 ``<span>`` tags within a ``<pre>`` tag, wrapped in a ``<div>`` tag. The ``<div>``'s CSS class can be set by the `cssclass` option. (filenames *.html, *.htm)
        //* img, IMG, png:
        //    Create a PNG image from source code. This uses the Python Imaging Library to generate a pixmap from the source code. (filenames *.png)
        //* irc, IRC:
        //    Format tokens with IRC color sequences
        //* jpg, jpeg:
        //    Create a JPEG image from source code. This uses the Python Imaging Library to generate a pixmap from the source code. (filenames *.jpg)
        //* latex, tex:
        //    Format tokens as LaTeX code. This needs the `fancyvrb` and `color` standard packages. (filenames *.tex)
        //* pango, pangomarkup:
        //    Format tokens as Pango Markup code. It can then be rendered to an SVG.
        //* raw, tokens:
        //    Format tokens as a raw representation for storing token streams. (filenames *.raw)
        //* rtf:
        //    Format tokens as RTF markup. This formatter automatically outputs full RTF documents with color information and other useful stuff. Perfect for Copy and Paste into Microsoft(R) Word(R) documents. (filenames *.rtf)
        //* svg:
        //    Format tokens as an SVG graphics file.  This formatter is still experimental. Each line of code is a ``<text>`` element with explicit ``x`` and ``y`` coordinates containing ``<tspan>`` elements with the individual token styles. (filenames *.svg)
        //* terminal, console:
        //    Format tokens with ANSI color sequences, for output in a text console. Color sequences are terminated at newlines, so that paging the output works correctly.
        //* terminal16m, console16m, 16m:
        //    Format tokens with ANSI color sequences, for output in a true-color terminal or console.  Like in `TerminalFormatter` color sequences are terminated at newlines, so that paging the output works correctly.
        //* terminal256, console256, 256:
        //    Format tokens with ANSI color sequences, for output in a 256-color terminal or console.  Like in `TerminalFormatter` color sequences are terminated at newlines, so that paging the output works correctly.
        //* testcase:
        //    Format tokens as appropriate for a new testcase.
        //* text, null:
        //    Output the text unchanged without any formatting. (filenames *.txt)



        // Create a new Process instance for pygmentize
        $process = new Process(['pygmentize', '-l', $language, '-f', 'terminal']);

        // Set input as the source code to be highlighted
        $process->setInput($sourceCode);

        // Run the pygmentize command
        $process->run();

        // Check if there was an error executing the command
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Get the output (highlighted code) of the command
        $highlightedCode = $process->getOutput();

        return $highlightedCode;
    }
}