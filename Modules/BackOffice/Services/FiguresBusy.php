<?php

namespace Modules\BackOffice\Services;

/** The receivables figures for this as-on date are being calculated by another request. */
class FiguresBusy extends \RuntimeException
{
}
