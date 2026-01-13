<?php

namespace App\Filament\Resources\Artworks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArtworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('artworks')
                    ->conversion('thumbnail')
                    ->size(60),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
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
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_published')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Draft',
                    ]),
            ])
            ->recordActions([
                Action::make('toggle_publish')
                    ->label(fn ($record) => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn ($record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->action(function ($record) {
                        $record->update([
                            'is_published' => ! $record->is_published,
                            'published_at' => ! $record->is_published ? now() : null,
                        ]);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
