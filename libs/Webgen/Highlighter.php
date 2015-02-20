<?php
	/**
	 * @author		Jan Pecha, <janpecha@email.cz>
	 * @license		http://janpecha.iunas.cz/webgen/#license
	 * @link		http://janpecha.iunas.cz/
	 */

	namespace Webgen;
	use Nette;
	use FSHL;

	class Highlighter extends Nette\Object
	{
		public function highlight($s, $lang = 'html', $lexer = NULL)
		{
			if ($lexer === NULL) {
				$lexer = $this->getFshlLexer($lang);
			}

			if ($lexer === FALSE) {
				throw new HighlighterException('Highlighter: unknow language code');
			}
			$highlighter = new \FSHL\Highlighter(new \FSHL\Output\Html());
			$highlighter->setLexer($lexer);
			return $highlighter->highlight($s);
		}



		/**
		 * User handler for code block
		 *
		 * @param TexyHandlerInvocation  handler invocation
		 * @param string  block type
		 * @param string  text to highlight
		 * @param string  language
		 * @param TexyModifier modifier
		 * @return TexyHtml
		 */
		public function blockHandler($invocation, $blocktype, $content, $lang, $modifier)
		{
			if ($blocktype !== 'block/code') {
				return $invocation->proceed();
			}

			$lexer = $this->getFshlLexer($lang);

			if (!$lexer) {
				return $invocation->proceed();
			}

			$texy = $invocation->getTexy();
			$content = \Texy::outdent($content);
			$content = $this->highlight($content, $lang, $lexer);
			$content = $texy->protect($content, \Texy::CONTENT_BLOCK);

			$elPre = \TexyHtml::el('pre');
			if ($modifier) $modifier->decorate($texy, $elPre);
			$elPre->attrs['class'] = strtolower($lang);

			$elCode = $elPre->create('code', $content);

			return $elPre;
		}



		/**
		 * Pattern handler for PHP & JavaScript block syntaxes
		 *
		 * @param TexyBlockParser
		 * @param array      regexp matches
		 * @param string     pattern name
		 * @return TexyHtml|string|FALSE
		 */
		public function codeBlockHandler($parser, $matches, $name)
		{
			list($content) = $matches;
			$lang = $name === 'phpBlockSyntax' ? 'PHP' : 'HTML';
			$lexer = $this->getFshlLexer($lang);

			if (!$lexer) {
				return FALSE;
			}

			$texy = $parser->getTexy();
			$content = $this->highlight($content, $lang, $lexer);
			$content = $texy->protect($content, \Texy::CONTENT_BLOCK);

			$elPre = \TexyHtml::el('pre');
			$elPre->attrs['class'] = strtolower($lang);

			$elCode = $elPre->create('code', $content);

			return $elPre;
		}



		/**
		 * @param	string
		 */
		protected function getFshlLexer($lang)
		{
			$lang = strtoupper($lang);
			switch ($lang) {
				case 'PHP':
					return new FSHL\Lexer\Php;

				case 'HTML':
					return new FSHL\Lexer\Html;

				case 'JS':
				case 'JAVASCRIPT':
					return new FSHL\Lexer\Javascript;

				case 'CSS':
					return new FSHL\Lexer\Css;

				case 'SQL':
					return new FSHL\Lexer\Sql;

				case 'NEON':
					return new FSHL\Lexer\Neon;
			}
			return FALSE;
		}
	}


	class HighlighterException extends \RuntimeException
	{
	}

