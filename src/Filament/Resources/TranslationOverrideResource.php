<?php

namespace JaysonTemporas\TranslationOverrides\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use JaysonTemporas\TranslationOverrides\Actions\TranslationKeyValueExtractor;
use JaysonTemporas\TranslationOverrides\Models\TranslationOverride;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TranslationOverrideResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static ?string $model = TranslationOverride::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Translation Overrides';

    public static function form(Forms\Form $form): Forms\Form
    {
        $schema = [
            Forms\Components\Select::make('locale')
                ->required()
                ->options(config('translation-overrides.supported_languages'))
                ->searchable(),

            Forms\Components\Select::make('key')
                ->required()
                ->options(
                    (new TranslationKeyValueExtractor)->handle()
                )
                ->searchable(),

            Forms\Components\Textarea::make('value')
                ->required()
                ->columnSpanFull(),
        ];

        return $form->schema($schema);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('locale')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn (string $state): string => config("translation-overrides.supported_languages.{$state}", $state)),

            Tables\Columns\TextColumn::make('key')
                ->searchable(),

            Tables\Columns\TextColumn::make('value')
                ->searchable(),
        ];

        $tableBuilder = $table->columns($columns);

        // Apply tenant filtering if tenancy is enabled
        if (static::isTenancyEnabled()) {
            $tenantIdColumn = static::getTenantIdColumn();
            $tableBuilder->modifyQueryUsing(fn (Builder $query) => $query->where($tenantIdColumn, auth()->user()->getTranslationTenantId()));
        }

        return $tableBuilder
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label('Language')
                    ->options(config('translation-overrides.supported_languages'))
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (TranslationOverride $record) => $record->clearTenantCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(fn (Collection $records) => $records->each->clearTenantCache()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranslationOverrides::route('/'),
            'create' => Pages\CreateTranslationOverride::route('/create'),
            'view' => Pages\ViewTranslationOverride::route('/{record}'),
            'edit' => Pages\EditTranslationOverride::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return config('translation-overrides.navigation.group');
    }

    public static function canAccess(): bool
    {
        if (method_exists(auth()->user(), 'hasRole')) {
            return auth()->user()->hasRole(config('translation-overrides.can_access.role') ?? []);
        }

        return true;
    }

    protected static function isTenancyEnabled(): bool
    {
        return config('translation-overrides.tenancy_enabled', true);
    }

    protected static function getTenantIdColumn(): string
    {
        return config('translation-overrides.tenant_id_column', 'tenant_id');
    }

    public static function getCluster(): ?string
    {
        return config('translation-overrides.cluster', null);
    }
}
