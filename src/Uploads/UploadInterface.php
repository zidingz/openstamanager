<?php

namespace Uploads;

interface UploadInterface
{
    /**
     * Restituisce il percorso per il salvataggio degli upload.
     */
    public function getUploadDirectory(): string;

    public function uploads(int $id_record);
}
