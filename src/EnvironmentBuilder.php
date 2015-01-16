<?php

namespace Brera;

interface EnvironmentBuilder
{
	/**
	 * @param string $xmlString
	 * @return Environment
	 */
	public function createEnvironmentFromXml($xmlString);
}
