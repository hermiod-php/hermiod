<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Validation;

use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Property\Validation\Result;
use Hermiod\Resource\Property\Validation\ResultInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
class ResultTest extends TestCase
{
    public function testImplementsResultInterface(): void
    {
        $result = new Result();

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testConstructorWithNoErrors(): void
    {
        $result = new Result();

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->getValidationErrors());
    }

    public function testConstructorWithSingleError(): void
    {
        $error = 'Validation failed';
        $result = new Result($error);

        $this->assertFalse($result->isValid());
        $this->assertSame([$error], $result->getValidationErrors());
    }

    public function testConstructorWithMultipleErrors(): void
    {
        $errors = ['Error 1', 'Error 2', 'Error 3'];
        $result = new Result(...$errors);

        $this->assertFalse($result->isValid());
        $this->assertSame($errors, $result->getValidationErrors());
    }

    public function testIsValidReturnsTrueForNoErrors(): void
    {
        $result = new Result();

        $this->assertTrue($result->isValid());
    }

    public function testIsValidReturnsFalseForSingleError(): void
    {
        $result = new Result('Error');

        $this->assertFalse($result->isValid());
    }

    public function testIsValidReturnsFalseForMultipleErrors(): void
    {
        $result = new Result('Error 1', 'Error 2');

        $this->assertFalse($result->isValid());
    }

    public function testGetValidationErrorsReturnsEmptyArrayForNoErrors(): void
    {
        $result = new Result();

        $this->assertSame([], $result->getValidationErrors());
    }

    public function testGetValidationErrorsReturnsSingleError(): void
    {
        $error = 'Validation error';
        $result = new Result($error);

        $this->assertSame([$error], $result->getValidationErrors());
    }

    public function testGetValidationErrorsReturnsMultipleErrors(): void
    {
        $errors = ['Error 1', 'Error 2', 'Error 3'];
        $result = new Result(...$errors);

        $this->assertSame($errors, $result->getValidationErrors());
    }

    public function testWithErrorsAddsNewErrorToEmptyResult(): void
    {
        $originalResult = new Result();
        $newError = 'New error';
        $newResult = $originalResult->withErrors($newError);

        $this->assertNotSame($originalResult, $newResult);
        $this->assertSame([], $originalResult->getValidationErrors());
        $this->assertSame([$newError], $newResult->getValidationErrors());
        $this->assertTrue($originalResult->isValid());
        $this->assertFalse($newResult->isValid());
    }

    public function testWithErrorsAddsMultipleErrorsToEmptyResult(): void
    {
        $originalResult = new Result();
        $newErrors = ['Error 1', 'Error 2'];
        $newResult = $originalResult->withErrors(...$newErrors);

        $this->assertNotSame($originalResult, $newResult);
        $this->assertSame([], $originalResult->getValidationErrors());
        $this->assertSame($newErrors, $newResult->getValidationErrors());
        $this->assertTrue($originalResult->isValid());
        $this->assertFalse($newResult->isValid());
    }

    public function testWithErrorsAddsNewErrorToExistingErrors(): void
    {
        $originalErrors = ['Original error'];
        $originalResult = new Result(...$originalErrors);
        $newError = 'New error';
        $newResult = $originalResult->withErrors($newError);

        $this->assertNotSame($originalResult, $newResult);
        $this->assertSame($originalErrors, $originalResult->getValidationErrors());
        $this->assertSame(['Original error', 'New error'], $newResult->getValidationErrors());
        $this->assertFalse($originalResult->isValid());
        $this->assertFalse($newResult->isValid());
    }

    public function testWithErrorsAddsMultipleNewErrorsToExistingErrors(): void
    {
        $originalErrors = ['Original error 1', 'Original error 2'];
        $originalResult = new Result(...$originalErrors);
        $newErrors = ['New error 1', 'New error 2'];
        $newResult = $originalResult->withErrors(...$newErrors);

        $this->assertNotSame($originalResult, $newResult);
        $this->assertSame($originalErrors, $originalResult->getValidationErrors());

        $this->assertSame(
            [
                'Original error 1',
                'Original error 2',
                'New error 1',
                'New error 2'
            ],
            $newResult->getValidationErrors(),
        );

        $this->assertFalse($originalResult->isValid());
        $this->assertFalse($newResult->isValid());
    }

    public function testWithHydrationStageReturnsNewInstance(): void
    {
        $originalResult = new Result();
        $callback = function (HydratorInterface $hydrator): void {};
        $newResult = $originalResult->withHydrationStage($callback);

        $this->assertNotSame($originalResult, $newResult);
        $this->assertInstanceOf(ResultInterface::class, $newResult);
    }

    public function testWithHydrationStageAddsStageToEmptyResult(): void
    {
        $originalResult = new Result();
        $callbackExecuted = false;

        $callback = function (HydratorInterface $hydrator) use (&$callbackExecuted): void {
            $callbackExecuted = true;
        };

        $newResult = $originalResult->withHydrationStage($callback);
        $mockHydrator = $this->createHydratorMock();

        // Original result should not execute callback
        $originalResult->hydrate($mockHydrator);
        $this->assertFalse($callbackExecuted);

        // New result should execute callback
        $newResult->hydrate($mockHydrator);
        $this->assertTrue($callbackExecuted);
    }

    public function testHydrateWithNoStages(): void
    {
        $result = new Result();
        $mockHydrator = $this->createHydratorMock();

        // Should not throw exception - this tests the empty array path
        $result->hydrate($mockHydrator);
        $this->assertTrue(true); // Assert that we reach this point
    }

    public function testHydrateWithSingleStage(): void
    {
        $result = new Result();
        $callbackExecuted = false;

        $finalResult = $result->withHydrationStage(function (HydratorInterface $hydrator) use (&$callbackExecuted): void {
            $callbackExecuted = true;
        });

        $mockHydrator = $this->createHydratorMock();

        $finalResult->hydrate($mockHydrator);

        $this->assertTrue($callbackExecuted);
    }

    public function testHydrateWithEmptyStagesArrayAfterCloning(): void
    {
        $result = new Result();
        // Test cloned result with no stages added
        $clonedResult = $result->withErrors('foo'); // This creates a clone but doesn't add stages

        $mockHydrator = $this->createHydratorMock();

        // Both should handle empty stages array without issues
        $result->hydrate($mockHydrator);
        $clonedResult->hydrate($mockHydrator);

        $this->assertTrue(true); // Assert we reach this point without exceptions
    }

    public function testHydrateExecutesStagesInReverseOrder(): void
    {
        $result = new Result();

        $order = [];

        $finalResult = $result
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'first';
            })
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'second';
            })
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'third';
            });

        $mockHydrator = $this->createHydratorMock();

        $finalResult->hydrate($mockHydrator);

        // Stages should be executed in reverse order (LIFO)
        $this->assertSame(['third', 'second', 'first'], $order);
    }

    public function testHydratePassesHydratorToCallbacks(): void
    {
        $result = new Result();

        $mock = $this->createHydratorMock();

        $callback = function (HydratorInterface $hydrator) use (&$mock): void {
            $this->assertSame($mock, $hydrator);
        };

        $result
            ->withHydrationStage($callback)
            ->withHydrationStage($callback)
            ->withHydrationStage($callback)
            ->hydrate($mock);
    }

    public function testMergeWithEmptyResultAndEmptyResult(): void
    {
        $result1 = new Result();
        $result2 = new Result();

        $mergedResult = $result1->merge($result2);

        $this->assertNotSame($result1, $mergedResult);
        $this->assertNotSame($result2, $mergedResult);

        $this->assertTrue($mergedResult->isValid());

        $this->assertSame([], $mergedResult->getValidationErrors());
    }

    public function testMergeWithEmptyResultAndResultWithErrors(): void
    {
        $result1 = new Result();
        $result2 = new Result('Error from result2');

        $mergedResult = $result1->merge($result2);

        $this->assertNotSame($result1, $mergedResult);
        $this->assertNotSame($result2, $mergedResult);

        $this->assertFalse($mergedResult->isValid());

        $this->assertSame(['Error from result2'], $mergedResult->getValidationErrors());
    }

    public function testMergeWithResultWithErrorsAndEmptyResult(): void
    {
        $result1 = new Result('Error from result1');
        $result2 = new Result();

        $mergedResult = $result1->merge($result2);

        $this->assertNotSame($result1, $mergedResult);
        $this->assertNotSame($result2, $mergedResult);

        $this->assertFalse($mergedResult->isValid());

        $this->assertSame(['Error from result1'], $mergedResult->getValidationErrors());
    }

    public function testMergeWithBothResultsHavingErrors(): void
    {
        $result1 = new Result('Error 1', 'Error 2');
        $result2 = new Result('Error 3', 'Error 4');

        $mergedResult = $result1->merge($result2);

        $this->assertNotSame($result1, $mergedResult);
        $this->assertNotSame($result2, $mergedResult);

        $this->assertFalse($mergedResult->isValid());

        $this->assertSame(['Error 1', 'Error 2', 'Error 3', 'Error 4'], $mergedResult->getValidationErrors());
    }

    public function testMergeWithHydrationStages(): void
    {
        $result1 = new Result();
        $result2 = new Result();
        $order = [];

        $result1WithStage = $result1->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
            $order[] = 'result1_callback';
        });

        $result2WithStage = $result2->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
            $order[] = 'result2_callback';
        });

        $mergedResult = $result1WithStage->merge($result2WithStage);

        $mergedResult->hydrate(
            $this->createHydratorMock()
        );

        // Should execute result2's stages (through merge callback) then result1's stages
        $this->assertSame(['result2_callback', 'result1_callback'], $order);
    }

    public function testMergeWithMultipleHydrationStages(): void
    {
        $result1 = new Result();
        $result2 = new Result();

        $order = [];

        $result1WithStages = $result1
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'result1_callback_a';
            })
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'result1_callback_b';
            });

        $result2WithStages = $result2
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'result2_callback_a';
            })
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'result2_callback_b';
            });

        $mergedResult = $result1WithStages->merge($result2WithStages);

        $mergedResult->hydrate(
            $this->createHydratorMock()
        );

        // Should execute result2's stages in reverse order, then result1's stages in reverse order
        $this->assertSame([
            'result2_callback_b',
            'result2_callback_a',
            'result1_callback_b',
            'result1_callback_a'
        ], $order);
    }

    public function testMergePreservesOriginalResults(): void
    {
        $originalErrors1 = ['Error 1'];
        $originalErrors2 = ['Error 2'];

        $result1 = new Result(...$originalErrors1);
        $result2 = new Result(...$originalErrors2);

        $merged = $result1->merge($result2);

        // Original results should be unchanged
        $this->assertSame($originalErrors1, $result1->getValidationErrors());
        $this->assertSame($originalErrors2, $result2->getValidationErrors());

        // Merged result should have combined errors
        $this->assertSame(['Error 1', 'Error 2'], $merged->getValidationErrors());
    }

    public function testComplexScenarioWithAllMethods(): void
    {
        // Start with a result that has initial errors
        $initialResult = new Result('Initial error');

        // Add more errors
        $withMoreErrors = $initialResult->withErrors('Second error', 'Third error');

        // Add hydration stages
        $order = [];

        $withStages = $withMoreErrors
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'stage1';
            })
            ->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
                $order[] = 'stage2';
            });

        // Create another result to merge with
        $otherResult = new Result('Other error');

        $otherWithStage = $otherResult->withHydrationStage(function (HydratorInterface $hydrator) use (&$order): void {
            $order[] = 'other_stage';
        });

        // Merge the results
        $finalResult = $withStages->merge($otherWithStage);

        // Test the final result
        $this->assertFalse($finalResult->isValid());

        $this->assertSame(
            [
                'Initial error',
                'Second error',
                'Third error',
                'Other error'
            ],
            $finalResult->getValidationErrors(),
        );

        $finalResult->hydrate(
            $this->createHydratorMock()
        );

        $this->assertSame(['other_stage', 'stage2', 'stage1'], $order);

        // Ensure original results are unchanged
        $this->assertSame(['Initial error'], $initialResult->getValidationErrors());
        $this->assertSame(['Other error'], $otherResult->getValidationErrors());
    }

    #[DataProvider('validationErrorsProvider')]
    public function testValidationErrorsHandling(array $errors, bool $expectedValid): void
    {
        $result = new Result(...$errors);

        $this->assertSame($expectedValid, $result->isValid());
        $this->assertSame($errors, $result->getValidationErrors());
    }

    /**
     * @return array<string, array{errors: array<string>, expectedValid: bool}>
     */
    public static function validationErrorsProvider(): array
    {
        return [
            'no errors' => [
                'errors' => [],
                'expectedValid' => true,
            ],
            'single error' => [
                'errors' => ['Single error'],
                'expectedValid' => false,
            ],
            'multiple errors' => [
                'errors' => ['Error 1', 'Error 2', 'Error 3'],
                'expectedValid' => false,
            ],
            'empty string error' => [
                'errors' => [''],
                'expectedValid' => false,
            ],
            'mixed content errors' => [
                'errors' => ['Normal error', '', 'Another error'],
                'expectedValid' => false,
            ],
        ];
    }

    private function createHydratorMock(): HydratorInterface & MockObject
    {
        return $this->createMock(HydratorInterface::class);
    }
}
