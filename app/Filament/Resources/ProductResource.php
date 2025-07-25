<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use App\Helpers\Util;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\productExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Enums\Role;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Inventory';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Product Information')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Product Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter product name')
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('0.00')
                                    ->minValue(0.01),

                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->required()
                                    ->relationship('category', 'name', fn(Builder $query) => $query->where('active', true))
                                    ->searchable()
                                    ->preload()
                                    ->exists(table: Category::class, column: 'id')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->unique()
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->placeholder('Select or create category'),

                                Forms\Components\Select::make('brand_id')
                                    ->label('Brand')
                                    ->required()
                                    ->relationship('brand', 'name', fn(Builder $query) => $query->where('active', true))
                                    ->searchable()
                                    ->preload()
                                    ->exists(table: Brand::class, column: 'id')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->maxLength(255),
                                    ])
                                    ->placeholder('Select or create brand'),
                                Forms\Components\TextInput::make('stock_security')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)

                            ]),

                        Forms\Components\RichEditor::make('description')
                            ->label('Product Description')
                            ->placeholder('Describe your product in detail...')
                            ->columnSpanFull(),

                    ]),

                // Media Section
                Section::make('Product Media')
                    ->description('Upload product images')
                    ->icon('heroicon-m-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Product Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('products')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048) // 2MB
                            ->helperText('Upload a high quality product image (max 2MB, JPG/PNG/WebP)')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->required(),

            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->size(60)
                    ->defaultImageUrl(fn($record) => Util::getDefaultAvatar($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->color(
                        fn($record) =>
                        $record->stock <= 0 ? 'danger' : ($record->stock <= $record->stock_security ? 'warning' : 'success')
                    )
                    ->icon(
                        fn($record) =>
                        $record->stock <= 0 ? 'heroicon-m-x-circle' : ($record->stock <= $record->stock_security ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                    ),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->badge()
                    ->color(color: 'primary'),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created By'),
                // ->url(
                //     fn ($record) => $record->user ? route('filament.admin.resources.users.view', ['record' => $record->user]) : null,
                //     shouldOpenInNewTab: true
                // ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All Products')
                    ->trueLabel('Active Products')
                    ->falseLabel('Inactive Products')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //->hidden(fn() => ! EditProduct::canEdit()),
                Tables\Actions\EditAction::make()
                    ->hidden(fn() => ! EditProduct::canEdit()),

            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->hidden(fn() => ! EditProduct::canEdit())
                    ->action(fn(Collection $records) => $records->each->update(['active' => true])),
                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->hidden(fn() => ! EditProduct::canEdit())
                    ->action(fn(Collection $records) => $records->each->update(['active' => false])),
            ])
            ->HeaderActions([
                Action::make('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return Excel::download(new ProductExport, 'products.csv', \Maatwebsite\Excel\Excel::CSV);
                    }),

                Action::make('Export XLSX')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return Excel::download(new ProductExport, 'products.xlsx');
                    }),
            ])
            ->defaultSort('active', 'desc');
    }

    public static function getWidgets(): array
    {
        return [
            ProductStats::class
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('Product Details')
                    ->icon('heroicon-m-cube')
                    ->schema([
                        Split::make([
                            ImageEntry::make('image')
                                ->defaultImageUrl(fn(Product $record) => Util::getDefaultAvatar($record->name))
                                ->label('Product Image')
                                ->height(250)
                                ->width(250)
                                ->extraImgAttributes(['class' => 'rounded-xl shadow-lg']),

                            InfoGrid::make(1)
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Product Name')
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->weight(FontWeight::Bold)
                                        ->copyable(),

                                    TextEntry::make('description')
                                        ->label('Description')
                                        ->html()
                                        ->columnSpanFull(),

                                    InfoGrid::make(3)
                                        ->schema([
                                            TextEntry::make('price')
                                                ->label('Price')
                                                ->money('USD')
                                                ->size(TextEntry\TextEntrySize::Large)
                                                ->color('success'),

                                            TextEntry::make('stock')
                                                ->label('Stock')
                                                ->badge()
                                                ->color(
                                                    fn($record) =>
                                                    $record->stock <= 0 ? 'danger' : ($record->stock <= $record->stock_security ? 'warning' : 'success')
                                                )
                                                ->icon(
                                                    fn($record) =>
                                                    $record->stock <= 0 ? 'heroicon-m-x-circle' : ($record->stock <= $record->stock_security ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                                                ),
                                            IconEntry::make('active')
                                                ->label('Status')
                                                ->boolean()
                                                ->trueColor('success')
                                                ->falseColor('danger'),
                                        ]),
                                ]),
                        ])->from('md'),
                    ]),

                InfoSection::make('Additional Information')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        InfoGrid::make(4)
                            ->schema([
                                TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('brand.name')
                                    ->label('Brand')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('d/m/Y h:m:s')
                                    ->tooltip(fn($state) => $state->diffForHumans()),

                                TextEntry::make('user.name')
                                    ->label('Created By')
                                    ->badge(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
