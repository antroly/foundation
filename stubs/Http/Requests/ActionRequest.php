<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Dtos\Dto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class for all action requests.
 *
 * Every request must implement toDto() to convert validated HTTP input
 * into a typed Dto passed to the Action.
 */
abstract class ActionRequest extends FormRequest
{
    abstract public function toDto(): Dto;
}
