<?php

namespace App\Helpers;

class PaginationData
{
    public static function formatPaginationLinks($pagination)
    {
        $links = [];

        // Add the 'first' link
        if ($pagination->onFirstPage()) {
            $links[] = ['label' => 'First', 'url' => null, 'active' => false];
        } else {
            $links[] = ['label' => 'First', 'url' => $pagination->url(1), 'active' => false];
        }

        // Add previous page link
        if ($pagination->previousPageUrl()) {
            $links[] = ['label' => 'Previous', 'url' => $pagination->previousPageUrl(), 'active' => false];
        } else {
            $links[] = ['label' => 'Previous', 'url' => null, 'active' => false];
        }

        // Add number links
        for ($page = 1; $page <= $pagination->lastPage(); $page++) {
            $links[] = [
                'label' => $page,
                'url' => $pagination->url($page),
                'active' => $pagination->currentPage() == $page,
            ];
        }

        // Add next page link
        if ($pagination->nextPageUrl()) {
            $links[] = ['label' => 'Next', 'url' => $pagination->nextPageUrl(), 'active' => false];
        } else {
            $links[] = ['label' => 'Next', 'url' => null, 'active' => false];
        }

        // Add the 'last' link
        if ($pagination->lastPage() == $pagination->currentPage()) {
            $links[] = ['label' => 'Last', 'url' => null, 'active' => false];
        } else {
            $links[] = ['label' => 'Last', 'url' => $pagination->url($pagination->lastPage()), 'active' => false];
        }

        return $links;
    }
}
