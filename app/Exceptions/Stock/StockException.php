<?php

namespace App\Exceptions\Stock;

use DomainException;

/**
 * A stock or prescription operation the business rules do not allow. The message is Russian and
 * meant for the user: controllers show it as a flash error or a JSON 422, and the surrounding
 * transaction has already been rolled back.
 */
class StockException extends DomainException {}
