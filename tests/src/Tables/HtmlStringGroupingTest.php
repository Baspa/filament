<?php

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tests\Models\Post;
use Filament\Tests\Tables\TestCase;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportTesting\Testable;

use function Filament\Tests\livewire;

uses(TestCase::class);

it('can group a table with HtmlString titles', function () {
    $posts = Post::factory()->count(5)->create([
        'title' => 'Test Post',
        'is_published' => true,
    ]);

    livewire(HtmlStringGroupingTestComponent::class)
        ->set('tableGrouping', 'html_title')
        ->assertSee('Group 1')
        ->assertSee('Group 2')
        ->assertSee('Test Post', 5); // Should see all 5 posts
});

class HtmlStringGroupingTestComponent extends \Livewire\Component implements HasForms, \Filament\Tables\Contracts\HasTable
{
    use InteractsWithForms;
    use \Filament\Tables\Concerns\InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query())
            ->groups(fn () => [
                Tables\Grouping\Group::make('html_title')
                    ->getTitleFromRecordUsing(function (Post $record): HtmlString {
                        // Create different HtmlString objects with same content
                        // This should group them together despite being different objects
                        $groupNumber = $record->id <= 3 ? 1 : 2;
                        return new HtmlString("Group {$groupNumber}");
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('title'),
            ]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    public function render(): string
    {
        return <<<'HTML'
<div>
    {{ $this->table }}
</div>
HTML;
    }
}
