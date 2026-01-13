<?php

namespace App\Filament\Resources\Artworks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ArtworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', str($state)->slug())),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('artist_name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(4),
                TextInput::make('medium')
                    ->maxLength(255),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('artworks')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->imageEditor()
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->default(false)
                    ->live(),
                DateTimePicker::make('published_at')
                    ->visible(fn ($get) => $get('is_published')),
            ]);
    }
}
