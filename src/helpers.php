<?php

function pingu_installed()
{
    return file_exists(storage_path('installed'));
}

