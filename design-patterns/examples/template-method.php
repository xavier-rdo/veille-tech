<?php

require_once(__DIR__.'/common/ProductDto.php');

/**
 * Base class for concrete product extracters. Parses a file to extract a list of products.
 * According to the format of the file to parse, it delegates the processing to its children.
 */
abstract class AbstractProductExtracter
{
    /**
     * Extracts a product list from a file and converts it into a collection of ProductDto
     *
     * @param SplFileObject $productFile
     *
     * @return  ProductDto[]
     */
    public function extractProducts(SplFileObject $productFile)
    {
        if (!$this->support($productFile)) {
            throw new \InvalidArgumentException('Unsupported file format');
        }

        // Php >= 5.5.11 for Ubuntu:
        // $contents = $productFile->fread($productFile->getSize());
        $contents = file_get_contents($productFile->getRealPath());

        return $this->parseContent($contents);
    }

    /**
     * Parse a file content in order to return a collection of products (ProductDto)
     *
     * @param  string $fileContent
     *
     * @return ProductDto[]
     */
    abstract protected function parseContent($fileContent);

    /**
     * Does the current extracter support file format ?
     *
     * @param SplFileObject $file
     *
     * @return bool
     */
    abstract protected function support(SplFileObject $file);

}

/**
 * Concrete product extracter that handles XML files
 */
class ProductXmlExtracter extends AbstractProductExtracter
{
    /**
     * {@inheritdoc}
     */
    protected function parseContent($fileContent)
    {
        $products = [];

        $xml = simplexml_load_string($fileContent);
        foreach ($xml->productList->product as $productNode) {
            $product = new ProductDto(
                (integer) $productNode->id,
                (string) $productNode->name,
                (string) $productNode->description,
                (float) $productNode->price
            );
            $products[] = $product;
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    protected function support(SplFileObject $file)
    {
        return $file->getExtension() === 'xml';
    }
}

$formats = ['xml'];
$format = $formats[rand(0, count($formats)-1)];
$fileToParse = new SplFileObject(__DIR__.'/data/products.'.$format, "r");

switch ($fileToParse->getExtension()) {
    case 'xml':
        $parser = new ProductXmlExtracter();
        break;
    default:
        return;
}

if (null !== $parser) {
    $productList = $parser->extractProducts($fileToParse);
    foreach ($productList as $product) {
        echo "$product\n";
    }
}
