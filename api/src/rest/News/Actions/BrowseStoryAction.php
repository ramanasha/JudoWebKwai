<?php

namespace REST\News\Actions;

use Psr\Http\Message\RequestInterface;
use Aura\Payload\Payload;

use Core\Responders\Responder;
use Core\Responders\JSONResponder;

use Analogue\ORM\System\Manager;

use League\Fractal;

class BrowseStoryAction implements \Core\ActionInterface
{
    public function __invoke(RequestInterface $request, Payload $payload)
    {
        $parameters = $request->getAttribute('parameters');

        $repository = new \Domain\News\NewsStoryRepository();

        $mapper = Manager::getMapper(\Domain\News\NewsStory::class);
        $storyQuery = $mapper->query();
        $storyQuery->orderBy('publish_date', 'desc');

        if (isset($parameters['filter']['year'])) {
            $storyQuery->whereYear('publish_date', '=', $parameters['filter']['year']);
        }
        if (isset($parameters['filter']['month'])) {
            $storyQuery->whereYear('publish_date', '=', $parameters['filter']['month']);
        }

        if (isset($parameters['filter']['featured'])) {
            $storyQuery->where('featured', '>', 0);
            $storyQuery->where(function ($query) {
                $query->WhereDate('featured_end_date', '>', \Carbon\Carbon::now())
                    ->orWhereNull('featured_end_date');
            });
            $storyQuery->orderBy('featured', 'desc');
        }

        $count = $storyQuery->count();

        $limit = $parameters['page']['limit'] ?? 10;
        $offset = $parameters['page']['offset'] ?? 0;
        $storyQuery->skip($offset)->take($limit);

        $stories = $storyQuery->with(['category'])->get();

        $payload->setExtras([
            'limit' => $limit,
            'offset' => $offset,
            'count' => $count
        ]);
        $payload->setOutput(new Fractal\Resource\Collection($stories, new \Domain\News\NewsStoryTransformer, 'news_stories'));

        return new JSONResponder(new Responder(), $payload);
    }
}
