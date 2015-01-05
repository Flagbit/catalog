<?php

namespace Brera;

class PoCDomParser implements DomParser
{
	/**
	 * @var \DOMDocument
	 */
	private $document;

	/**
	 * @var \DOMXPath
	 */
	private $xPathEngine;

	/**
	 * @var string
	 */
	private $namespacePrefix;

	/**
	 * @var string
	 */
	private $namespacePrefixDefault = 'uniqueDomParserPrefix';

	/**
	 * @param string $xmlString
	 */
	public function __construct($xmlString)
	{
		libxml_clear_errors();
		$internal = libxml_use_internal_errors(true);

		$this->document = new \DOMDocument;
		$this->document->preserveWhiteSpace = false;
		$this->document->loadXML($xmlString);

		if (!empty(libxml_get_errors())) {
			throw new \OutOfBoundsException();
		}

		libxml_use_internal_errors($internal);
	}

	/**
	 * @param string $xPathString
	 * @return \DOMNodeList
	 */
	public function getXPathNode($xPathString)
	{
		$this->initialiseXPath();
		$xPathString = $this->addNamespacePrefixesToXPathString($xPathString);
		$nodeList = $this->xPathEngine->query($xPathString);

		return $nodeList;
	}

	/**
	 * @param \DOMNode $domNode
	 * @return string
	 */
	public function getDomNodeXml($domNode)
	{
		return $this->document->saveXML($domNode);
	}

	/**
	 * @return null
	 */
	private function initialiseXPath()
	{
		$this->xPathEngine = new \DOMXPath($this->document);

		if ($namespaceUri = $this->getNamespaceUri()) {
			$this->xPathEngine->registerNamespace($this->namespacePrefixDefault, $namespaceUri);
			$this->namespacePrefix = $this->namespacePrefixDefault;
		}
	}

	/**
	 * @return string
	 */
	private function getNamespaceUri()
	{
		$namespaceUri = $this->document->documentElement->lookupNamespaceUri(null);

		return $namespaceUri;
	}

	/**
	 * @param string $xPathString
	 * @return string
	 */
	private function addNamespacePrefixesToXPathString($xPathString)
	{
		if ($this->namespacePrefix) {
			$xPathString = preg_replace('/(\/|^)([^@])/', '$1' . $this->namespacePrefix . ':$2', $xPathString);
		}

		return $xPathString;
	}
}
