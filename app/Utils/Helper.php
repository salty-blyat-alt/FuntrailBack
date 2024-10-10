<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

function cleanPagination(LengthAwarePaginator $data)
{
  $paginate = [
    'total' => $data->total(),
    'per_page' => $data->perPage(),
    'current_page' => $data->currentPage(),
    'next_page_url' => $data->nextPageUrl()
  ];

  return [
    'items' => $data->items(),
    'paginate' => $paginate
  ];
}


function uploadDocument($documentUpload, $documentFolderName)
{
  $originalFilename = pathinfo($documentUpload->getClientOriginalName(), PATHINFO_FILENAME);
  $extension = $documentUpload->getClientOriginalExtension();
  $filename = str_replace(' ', '_', $originalFilename);
  $filename = str_replace('-', '_', $filename);  // Use $filename instead of $originalFilename

  $uniqueIdentifier = uniqid();
  $combinedIdentifier = $filename . '_' . $uniqueIdentifier;

  $uniqueFilenameWithExtension = $combinedIdentifier . '.' . $extension;

  $filePath = $documentUpload->storeAs('public/' . $documentFolderName, $uniqueFilenameWithExtension);

  $fileUrl = Storage::url($filePath);
  return $fileUrl;
}
function formateDateTime($dateTime, $format)
{
  return \Carbon\Carbon::parse($dateTime)->format($format);
}

function currentDatetime()
{
  return now('Asia/Phnom_Penh');
}
