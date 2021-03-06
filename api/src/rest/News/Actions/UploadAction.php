<?php

namespace REST\News\Actions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Aura\Payload\Payload;

use Core\Responders\Responder;
use Core\Responders\JSONResponder;
use Core\Responders\JSONErrorResponder;
use Core\Responders\HTTPCodeResponder;
use Core\Responders\NotFoundResponder;

use Intervention\Image\ImageManager;

class UploadAction implements \Core\ActionInterface
{
    public function __invoke(RequestInterface $request, Payload $payload) : ResponseInterface
    {
        $files = $request->getUploadedFiles();
        if (!isset($files['image'])) {
            return (new HTTPCodeResponder(new Responder(), 400))->respond();
        }
        $uploadedFilename = $files['image']->getClientFilename();

        $id = $request->getAttribute('route.id');

        try {
            $story = \Domain\News\NewsStoriesTable::getTableFromRegistry()->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $rnfe) {
            return (
                new NotFoundResponder(
                    new Responder(),
                    _("Story doesn't exist.")
                ))->respond();
        }

        $filesystem = $request->getAttribute('clubman.container')['filesystem'];
        $stream = $files['image']->getStream();
        $ext = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        $originalFilename = 'images/news/' . $id . '/header_original.' . $ext;
        $filesystem->put($originalFilename, $stream->detach());

        $originalFile = $filesystem->read($originalFilename);

        try {
            $post = $request->getParsedBody();

            $manager = new ImageManager();
            $image = $manager->make($originalFile);
            $image = $image->crop(
                $post['overview_width'],
                $post['overview_height'],
                $post['overview_x'],
                $post['overview_y']
            )->resize(
                $image->width() * $post['overview_scale'],
                $image->height() * $post['overview_scale']
            );
            $filesystem->put('images/news/' . $id . '/header_overview_crop.' . $ext, $image->stream());

            $image = $manager->make($originalFile);
            $image = $image->crop(
                $post['detail_width'],
                $post['detail_height'],
                $post['detail_x'],
                $post['detail_y']
            )->resize(
                $image->width() * $post['detail_scale'],
                $image->height() * $post['detail_scale']
            );
            $filesystem->put('images/news/' . $id . '/header_detail_crop.' . $ext, $image->stream());
        } catch (\Exception $e) {
            echo $e;
            exit;
        }

        $payload->setOutput(\Domain\News\NewsStoryTransformer::createForItem($story, $filesystem));
        return (
            new JSONResponder(
                new Responder(),
                $payload
            )
        )->respond();
    }
}
