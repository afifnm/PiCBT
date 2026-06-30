<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Requests\GenerateAiQuestionsRequest;
use App\Models\QuestionBank;
use App\Services\GeminiQuestionGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class AiQuestionPasscodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.question_passcode' => '0806']);
    }

    public function test_correct_passcode_unlocks_ai_generator_in_session(): void
    {
        $request = $this->requestWithSession(['passcode' => '0806']);

        $response = app(QuestionBankController::class)->unlockAiGenerator($request);

        $this->assertSame(200, $response->status());
        $this->assertTrue($response->getData(true)['unlocked']);
        $this->assertTrue($request->session()->get('ai_question_generator_unlocked'));
    }

    public function test_wrong_passcode_is_rejected(): void
    {
        $request = $this->requestWithSession(['passcode' => '1234']);

        $response = app(QuestionBankController::class)->unlockAiGenerator($request);

        $this->assertSame(422, $response->status());
        $this->assertSame('Passcode tidak sesuai.', $response->getData(true)['message']);
        $this->assertFalse((bool) $request->session()->get('ai_question_generator_unlocked', false));
    }

    public function test_generate_endpoint_is_blocked_before_session_is_unlocked(): void
    {
        $request = GenerateAiQuestionsRequest::create('/admin/banks/1/questions/ai-generate', 'POST');
        $request->setLaravelSession($this->testSession());

        $response = app(QuestionBankController::class)->generateAiQuestions(
            $request,
            new QuestionBank,
            app(GeminiQuestionGeneratorService::class),
        );

        $this->assertSame(403, $response->status());
    }

    private function requestWithSession(array $data): Request
    {
        $request = Request::create('/admin/banks/questions/ai/unlock', 'POST', $data);
        $request->setLaravelSession($this->testSession());

        return $request;
    }

    private function testSession(): Store
    {
        return new Store('test', new ArraySessionHandler(120));
    }
}
