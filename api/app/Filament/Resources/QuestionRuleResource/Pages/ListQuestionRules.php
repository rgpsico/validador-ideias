<?php

namespace App\Filament\Resources\QuestionRuleResource\Pages;

use App\Filament\Resources\QuestionRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestionRules extends ListRecords
{
    protected static string $resource = QuestionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
