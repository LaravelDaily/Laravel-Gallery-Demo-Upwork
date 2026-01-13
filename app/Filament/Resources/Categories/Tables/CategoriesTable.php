<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('artworks_count')
                    ->counts('artworks')
                    ->label('Artworks')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->artworks()->count() > 0) {
                            Notification::make()
                                ->warning()
                                ->title('Cannot delete category')
                                ->body('This category has artworks assigned to it.')
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->artworks()->count() > 0) {
                                    Notification::make()
                                        ->warning()
                                        ->title('Cannot delete categories')
                                        ->body('Some categories have artworks assigned to them.')
                                        ->send();

                                    $action->halt();

                                    return;
                                }
                            }
                        }),
                ]),
            ]);
    }
}
