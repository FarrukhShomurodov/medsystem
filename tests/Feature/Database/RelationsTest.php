<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Every relation declared on an application model, as [model class, method name]. Relations are
 * recognised by their declared return type; polymorphic MorphTo relations cannot be queried
 * with has() and are left out.
 *
 * @return array<string, array{class-string<Model>, string}>
 */
function declaredModelRelations(): array
{
    $relations = [];

    foreach (glob(dirname(__DIR__, 3).'/app/Models/*.php') as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');
        $reflection = new ReflectionClass($class);

        if (! $reflection->isSubclassOf(Model::class)) {
            continue;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $type = $method->getReturnType();

            if ($method->class === $class
                && $method->getNumberOfParameters() === 0
                && $type instanceof ReflectionNamedType
                && is_a($type->getName(), Relation::class, true)
                && ! is_a($type->getName(), MorphTo::class, true)) {
                $relations[$reflection->getShortName().'::'.$method->name] = [$class, $method->name];
            }
        }
    }

    return $relations;
}

it('joins every relation on existing tables and columns', function (): void {
    $relations = declaredModelRelations();
    $broken = [];

    foreach ($relations as $label => [$model, $relation]) {
        // has() compiles the relation's keys into SQL, so a wrong table, pivot or column fails here.
        // Each probe runs in a savepoint: a failed statement would abort the test's transaction and
        // make every later probe fail too.
        try {
            DB::transaction(fn (): bool => $model::query()->has($relation)->exists());
        } catch (QueryException $exception) {
            $broken[] = "{$label}: ".($exception->getPrevious()?->getMessage() ?? $exception->getMessage());
        }
    }

    expect(count($relations))->toBeGreaterThan(150)
        ->and($broken)->toBe([]);
});
