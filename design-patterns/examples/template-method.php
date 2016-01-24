<?php

/**
 * "TEMPLATE METHOD" DESIGN PATTERN EXAMPLE
 *
 * GOF's definition of the Template method pattern :
 *
 * <blockquote>
 *     Define the skeleton of an algorithm in an operation, deferring some steps to subclasses.
 *     Template Method lets subclasses redefine certain steps of an algorithm without changing
 *     the algorithm's structure.
 * </blockquote>
 *
 * The following example shows classes that extract products from import files and hydrate them into a ProductDto.
 * Import files may have different formats : CSV, json or XML.
 *
 * The base class AbstractProductExtractor defines the main operation in its 'extractProducts' method.
 * @see AbstractProductExtractor::extractProducts
 *
 * It defers some steps of the algorithm to its children, responsible for dealing with the different formats :
 *
 * @see ProductCsvExtractor
 * @see ProductJsonExtractor
 * @see ProductXmlExtractor
 *
 */

require_once(__DIR__.'/common/ProductDto.php');

/**
 * Base class for concrete product extractors. Parses a file to extract a list of products.
 * According to the format of the file to parse, it delegates parts of the processing to one
 * of its children.
 */
abstract class AbstractProductExtractor
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
        echo static::CLASS.":\n"; // Only for debug & demo purposes
        if (!$this->support($productFile)) {
            throw new \InvalidArgumentException('Unsupported file format');
        }

        // Php >= 5.5.11 for Ubuntu:
        // $contents = $productFile->fread($productFile->getSize());
        // Php < 5.5.11 for Ubuntu:
        $content = file_get_contents($productFile->getRealPath());

        return $this->parseContent($content);
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
     * Does the current extractor support file format ?
     *
     * @param SplFileObject $file
     *
     * @return bool
     */
    abstract protected function support(SplFileObject $file);

}

/**
 * Concrete product extractor that handles XML content
 */
class ProductXmlExtractor extends AbstractProductExtractor
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

/**
 * Concrete product extractor that handles JSON content
 */
class ProductJsonExtractor extends AbstractProductExtractor
{
    /**
     * {@inheritdoc}
     */
    protected function parseContent($fileContent)
    {
        $products = [];

        $arrayProducts = json_decode($fileContent, true);
        foreach ($arrayProducts as $arrayProduct) {
            $product = new ProductDto(
                $arrayProduct["id"],
                $arrayProduct["name"],
                $arrayProduct["description"],
                $arrayProduct["price"]
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
        return $file->getExtension() === 'json';
    }
}

/**
 * Concrete product extractor that handles CSV content
 */
class ProductCsvExtractor extends AbstractProductExtractor
{
    /**
     * {@inheritdoc}
     */
    protected function parseContent($fileContent)
    {
        $products = [];

        $csvLines = str_getcsv($fileContent, "\n");

        foreach ($csvLines as $line) {
            $arrayProduct = str_getcsv($line, ";", '"');
            $product = new ProductDto(
                $arrayProduct[0],
                $arrayProduct[1],
                $arrayProduct[2],
                $arrayProduct[3]
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
        return $file->getExtension() === 'csv';
    }
}

/**
 * Client code that uses the AbstractProductExtractor and its children.
 *
 * This code is written in good old procedural Php for the needs of the demo,
 * as it is not directly related to the Template method pattern.
 * Ideally, it should reside in a dedicated service, a controller action or
 * a CLI command object.
 */

// Emulate the SplFileObject parameter that determines the concrete handler (product extractor) to instantiate:
$formats = ['xml','json','csv'];
$format = isset($argv[1]) ? $argv[1] : null;
if (null === $format || !in_array($format, $formats)) {
    throw new \InvalidArgumentException("Invalid format. Format must belong to : ".implode($formats, ", "));
}
$fileToParse = new SplFileObject(__DIR__.'/data/products.'.$format, "r");

// Resolving the right concrete class responsible for processing the request
// could be implemented through another Design Pattern ... ;-)
switch ($fileToParse->getExtension()) {
    case 'xml':
        $parser = new ProductXmlExtractor();
        break;
    case 'json':
        $parser = new ProductJsonExtractor();
        break;
    case 'csv':
        $parser = new ProductCsvExtractor();
        break;
    default:
        $parser = null;
        return;
}

if (null !== $parser) {
    $productList = $parser->extractProducts($fileToParse);
    foreach ($productList as $product) {
        echo "$product\n";
    }
    return;
}
