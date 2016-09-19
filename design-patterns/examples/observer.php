<?php

/**
 * Exemple d'implémentation du Design Pattern Observer en PHP.
 *
 * Use case : lorsqu'une commande est finalisée par un client, un mail de confirmation doit être envoyé au client
 *            et les stocks des produits commandés doivent être mis à jour.
 *
 * Le Design Pattern 'Observer' met en jeu un sujet observé et des observateurs. Dans l'exemple qui suit :
 *
 * - Un service est responsable de finaliser la commande : CheckoutOrderHandler. C'est le Sujet observé.
 * - Plusieurs services réagissent à la finalisation d'une commande (OrderConfirmationMailer, StockUpdater, etc.). Ce sont les observateurs.
 */

/**
 * Classe métier qui modélise (très sommairement) une commande.
 */
class Order {
	const STATUS_NEW = 'new';
	const STATUS_CHECKED_OUT = 'checked-out';

	private $items;

	private $status;

	private $statusAt;

	public function __construct() {
		$this->items = [];
		$this->status = self::STATUS_NEW;
		$this->statusAt = new \DateTime();
	}

	public function checkout() {
		$this->status = self::STATUS_CHECKED_OUT;
		$this->statusAt = new \DateTime();
	}

	public function getStatus() { return $this->status; }
	public function getItems() { return $this->items; }
    public function addItem() { /* */ }
}

/**
 * The subject interface
 */
interface Subject {
	public function notifyObservers(Order $order);
	public function addObserver(Observer $observer);
	public function removeObserver(Observer $observer);
}

/**
 * The observer interface
 */
interface Observer {
	public function notify(Order $order);
}

/**
 * The concrete subject
 */
class CheckoutOrderHandler implements Subject
{
	private $observers = [];

	public function addObserver(Observer $observer) {
		if (!in_array($observer, $this->observers)) {
			$this->observers[] = $observer;
		}
	}

	public function removeObserver(Observer $observer) { /* Unimplemented yet */ }

	public function handle(Order $order) {
		$order->checkout();
		$this->notifyObservers($order);
	}

	public function notifyObservers(Order $order) {
		foreach ($this->observers as $observer) {
			$observer->notify($order);
		}
	}
}

/**
 * A concrete observer. Service responsible for sending confirmation email to order's customer.
 */
class OrderConfirmationMailer implements Observer {
	public function notify(Order $order) {
		echo static::CLASS . " invoked. A confirmation mail will be sent to the customer.\n";
		// Unimplemented yet
	}
}

/**
 * Another concrete observer. Service that is responsible for updating product stocks
 */
class StockUpdater implements Observer {
	public function notify(Order $order) {
		echo static::CLASS . " invoked. Stocks will be updated.\n";
		// Unimplemented yet
	}
}

$order = new Order();
$checkoutOrderHandler = new CheckoutOrderHandler();
$orderConfirmationMailer = new OrderConfirmationMailer();
$checkoutOrderHandler->addObserver($orderConfirmationMailer);
$checkoutOrderHandler->handle($order);

$stockUpdater = new StockUpdater();
$checkoutOrderHandler->addObserver($stockUpdater);
$checkoutOrderHandler->handle($order);
