<?php

namespace App\Exceptions\Clinical;

use DomainException;

/**
 * A clinical record change that the domain rules refuse. The message is Russian and meant for
 * the user: controllers turn it into a flash error or a JSON 422.
 */
class ClinicalException extends DomainException {}
