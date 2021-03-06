<?php

namespace REST\Pages\Actions;

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
            $page = \Domain\Page\PagesTable::getTableFromRegistry()->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $rnfe) {
            return (
                new NotFoundResponder(
                    new Responder(),
                    _("Page doesn't exist.")
                ))->respond();
        }

        $filesystem = $request->getAttribute('clubman.container')['filesystem'];
        $stream = $files['image']->getStream();
        $ext = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        $originalFilename = 'images/pages/' . $id . '/original.' . $ext;
        $filesystem->put($originalFilename, $stream->detach());

        $originalFile = $filesystem->read($originalFilename);

        try {
            $post = $request->getParsedBody();

            $manager = new ImageManager();
            $image = $manager->make($originalFile);
            $image = $image->crop(
                $post['width'],
                $post['height'],
                $post['x'],
                $post['y']
            )->resize($image->width() * $post['scale'], $image->height() * $post['scale']);

            $filesystem->put('images/pages/' . $id . '/crop.' . $ext, $image->stream());
        } catch (\Exception $e) {
            echo $e;
            exit;
        }

        $payload->setOutput(\Domain\Page\PageTransformer::createForItem($page, $filesystem));
        return (new JSONResponder(new Responder(), $payload))->respond();
    }
}
