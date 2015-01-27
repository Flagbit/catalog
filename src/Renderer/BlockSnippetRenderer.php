<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;

abstract class BlockSnippetRenderer implements SnippetRenderer
{
    /**
     * @param string $layoutXmlFilePath
     * @param ProjectionSourceData $dataObject
     * @return string
     */
    protected function getSnippetContent($layoutXmlFilePath, ProjectionSourceData $dataObject)
    {
        $layoutReader = new LayoutReader();
        $layout = $layoutReader->loadLayoutFromXmlFile($layoutXmlFilePath);

        $outermostBlockLayout = $this->getOuterMostBlockLayout($layout);
        $outermostBlock = $this->createBlockWithChildren($outermostBlockLayout, $dataObject);

        return $outermostBlock->render();
    }

    /**
     * @param Layout $layout
     * @return Layout
     * @throws BlockSnippetRendererShouldHaveJustOneRootBlockException
     */
    private function getOuterMostBlockLayout(Layout $layout)
    {
        $snippetNodeValue = $layout->getNodeValue();

        if (!is_array($snippetNodeValue) || 1 !== count($snippetNodeValue)) {
            throw new BlockSnippetRendererShouldHaveJustOneRootBlockException();
        }

        return $snippetNodeValue[0];
    }

    /**
     * @param Layout $layout
     * @param ProjectionSourceData $dataObject
     * @return Block
     */
    private function createBlockWithChildren(Layout $layout, ProjectionSourceData $dataObject)
    {
        $blockClass = $layout->getAttribute('class');
        $blockTemplate = $layout->getAttribute('template');

        $this->validateBlockClass($blockClass);

        /** @var Block $blockInstance */
        $blockInstance = new $blockClass($blockTemplate, $dataObject);

        $nodeValue = $layout->getNodeValue();

        if (is_array($nodeValue)) {
            /** @var Layout $childBlockLayout */
            foreach ($nodeValue as $childBlockLayout) {
                $childBlockNameInLayout = $childBlockLayout->getAttribute('name');
                $childBlockInstance = $this->createBlockWithChildren($childBlockLayout, $dataObject);

                $blockInstance->addChildBlock($childBlockNameInLayout, $childBlockInstance);
            }
        }

        return $blockInstance;
    }

    /**
     * @param $blockClass
     * @throws CanNotInstantiateBlockException
     */
    private function validateBlockClass($blockClass)
    {
        if (is_null($blockClass)) {
            throw new CanNotInstantiateBlockException('Block class is not specified.');
        }

        if (!class_exists($blockClass)) {
            throw new CanNotInstantiateBlockException(sprintf('Class %s does not exist.', $blockClass));
        }

        if ('\\' . Block::class !== $blockClass && !in_array(Block::class, class_parents($blockClass))) {
            throw new CanNotInstantiateBlockException(sprintf('%s must extend %s', $blockClass, Block::class));
        }
    }
}
