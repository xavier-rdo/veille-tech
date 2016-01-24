<?php

/**
 * "CHAIN OF RESPONSIBILITY" DESIGN PATTERN EXAMPLE
 *
 * Example inspired from spaghetti.io :
 * @link http://spaghetti.io/cont/article/a-chain-of-responsibility-implementation-inside-the-symfony-container/15/1.html#.VqWyznUR-kp
 *
 * Definition (GoF) :
 *
 * <blockquote>
 *    Avoid coupling the sender of a request to its receiver by giving more than one object a chance to handle the request.
 *    Chain the receiving objects and pass the request along the chain until an object handles it.
 * </blockquote>
 *
 * This example implements three concrete file handlers (Xml, Json, Csv) organized as a chain. Returning
 * the right handler that is responsible for handling the request is the job of the chain. The chain
 * implementation is done through a $next property in each handler, pointing to its follower.
 *
 * For a more advanced example, see the spaghetti.io site. In their example, building the chain of responsibility is
 * implemented through a dedicated class (ParserChain), which makes the chain more Symfony's container friendly.
 */

/**
 * This is the base class for any concrete handler. It includes the methods common to all handlers, amongst which
 * the ones responsible for building and managing the chain (Here, the constructor expects an AbstractProductExtractor
 * instance injected in its $nextHandler property).
 *
 * All the "magic" is done in the extractProducts method : if the current instance can handle the request, then it handles
 * it, otherwise it delegates the request to the next handler in the chain.
 */
abstract class AbstractProductExtractor
{
    /** @var AbstractProductExtractor */
    private $nextHandler;

    /**
     * We use the constructor to build the handler chain. Each handler points to one follower.
     * The last handler of the chain is the one that has a null $nextHandler.
     *
     * @param AbstractProductExtractor|null $nextHandler
     */
    public function __construct(AbstractProductExtractor $nextHandler = null)
    {
        $this->nextHandler = $nextHandler;
    }

    /**
     * Helper method that enables to inject a new handler in the chain through any element of the chain.
     * If the current handler already has a follower, the new handler is propagated till the last handler of the chain.
     *
     * @param AbstractProductExtractor $nextHandler
     */
    public function setNextHandler(AbstractProductExtractor $nextHandler)
    {
        if (null === $this->nextHandler()) {
            $this->nextHandler = $nextHandler;
            return;
        }

        $this->nextHandler->setNextHandler($nextHandler);
    }

    /**
     * The main exposed method that does the job. It handles the import file if it supports its format.
     * Else it transmits the request to the next handler.
     *
     * @param SplFileObject $file
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException If no handler can handle the request
     */
    public function extractProducts(SplFileObject $file)
    {
        if ($this->support($file)) {
            return $this->handle($file);
        }

        if (null !== $this->nextHandler)
        {
            // !!! Do not do the following:
            // return $this->handle($file)
            // Indeed, you must re-enter the current method in order to make sure that the
            // handler supports the $file passed as an argument.
            return $this->nextHandler->extractProducts($file);
        }

        // Here, you must throw an exception if you want to make sure that the request is handled
        throw new \InvalidArgumentException("No handler found for file '{$file->getFilename()}'");
        // Otherwise, you could return false
        // return false;
    }

    /**
     * The main business logic of the class: opens the import file passed as an argument and parses its content.
     * Note that the parsing is delegated to sub-classes (cf. Template Method design pattern)
     * Note also that it's a business method and is not relevant to the CoR pattern.
     *
     * @param  SplFileObject $file
     *
     * @return mixed
     */
    protected function handle(SplFileObject $file)
    {
        // The following line of code is only for debug & demo purposes:
        echo static::CLASS." is the handler for {$file->getExtension()} files\n";
        $content = file_get_contents($file->getRealPath());

        return $this->parseContent($content);
    }

    abstract protected function support(SplFileObject $file);
    abstract protected function parseContent($content);
}

/**
 * Concrete handlers that are part of the chain of responsability.
 *
 * Note that the business methods <i>parseContent</i> are not implemented as they are not
 * directly related to the Chain of Responsibility pattern. This pattern is about retrieving
 * the right handler to handle a request, not about what the handler does.
 *
 * If you want a more realistic, yet perfectible, implementation, see the example
 * in the Template method pattern (template-method.php) where classes do the same
 * kind of job (parsing a file).
 */
class ProductCsvExtractor extends AbstractProductExtractor
{
    protected function support(SplFileObject $file) { return $file->getExtension() === 'csv'; }
    protected function parseContent($content) { return "success"; }
}

class ProductJsonExtractor extends AbstractProductExtractor
{
    protected function support(SplFileObject $file) { return $file->getExtension() === 'json'; }
    protected function parseContent($content) { return "success"; }
}

class ProductXmlExtractor extends AbstractProductExtractor
{
    protected function support(SplFileObject $file) { return $file->getExtension() === 'xml'; }
    protected function parseContent($content) { return "success"; }
}

/**
 * Client code (in old PHP procedural code ... Ideally, it shoud reside in a controller action, a service or a CLI command).
 *
 * Build the chain of handlers responsible for processing the request and pass them different files to handle :
 */

// The handler chain :
$handlerChain = new ProductCsvExtractor(new ProductXmlExtractor(new ProductJsonExtractor()));

// Submit different supported file formats to our chain:
$xmlFile = new SplFileObject(__DIR__.'/data/products.xml');
$handlerChain->extractProducts($xmlFile);
$csvFile = new SplFileObject(__DIR__.'/data/products.csv');
$handlerChain->extractProducts($csvFile);
$jsonFile = new SplFileObject(__DIR__.'/data/products.json');
$handlerChain->extractProducts($jsonFile);

try {
    $unsupportedFile = new SplFileObject(__FILE__); // php files are not supposed to be supported ;-)
    $handlerChain->extractProducts($unsupportedFile);
} catch (\InvalidArgumentException $e) {
    echo "Exception (as expected !) : {$e->getMessage()}\n";
}
