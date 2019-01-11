<?php
/**
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 */

namespace Effiana\CronBundle\Controller\Admin;


use Doctrine\ORM\EntityManager;
use Effiana\CronBundle\Entity\CronJob;
use Effiana\CronBundle\Form\Type\CronJobType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package Effiana\CronBundle\Controller\Admin
 *
 * @Route("/manager/settings/cron")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="effiana_cron_bundle_index")
     */
    public function index()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $scheduledCommands = $em->getRepository('EffianaCronBundle:CronJob')
            ->createQueryBuilder('cronJob')
            ->orderBy('cronJob.id', 'ASC')
            ->getQuery()->getArrayResult();

        $scheduledCommandIds = array_map(function($item) {
            return $item['id'];
        }, $scheduledCommands);

        $lastScheduledCommands = $em->getRepository('EffianaCronBundle:CronReport')
            ->createQueryBuilder('cronReport', 'cronReport.jobId')
            ->select('cronReport.jobId, MAX(cronReport.runAt) as runAt')
            ->andWhere('cronReport.jobId IN(:scheduledCommandIds)')
            ->groupBy('cronReport.jobId')
            ->setParameters([
                'scheduledCommandIds' => $scheduledCommandIds
            ])
            ->getQuery()->getArrayResult();

        $scheduledCommands = array_map(function($item) use($lastScheduledCommands) {
            $runAt = null;
            if(isset($lastScheduledCommands[$item['id']]['runAt'])) {
                $runAt = $lastScheduledCommands[$item['id']]['runAt'];
            }
            $item['runAt'] = $runAt;
            return $item;
        }, $scheduledCommands);

        return $this->render('@EffianaCron/Admin/index.html.twig', [
            'scheduledCommands' => $scheduledCommands
        ]);
    }

    /**
     * @param int $id
     * @Route("/{id}/toggle", name="effiana_cron_bundle_toggle")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function toggle(int $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $scheduledCommand = $em->getRepository('EffianaCronBundle:CronJob')->find($id);
        $scheduledCommand->setEnabled(!$scheduledCommand->getEnabled());
        $em->persist($scheduledCommand);
        $em->flush($scheduledCommand);
        return $this->redirectToRoute('effiana_cron_bundle_index');
    }

    /**
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Route("/add", name="effiana_cron_bundle_add")
     * @Route("/{id}/edit", name="effiana_cron_bundle_edit")
     */
    public function edit(?int $id, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if($id !== null ) {
            $scheduledCommand = $em->getRepository('EffianaCronBundle:CronJob')->find($id);
        } else {
            $scheduledCommand = new CronJob();
        }

        if($scheduledCommand instanceof CronJob) {
            $form = $this->createForm(CronJobType::class, $scheduledCommand);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($scheduledCommand);
                $em->flush($scheduledCommand);
                return $this->redirectToRoute('effiana_cron_bundle_index');
            }

            return $this->render(
                '@EffianaCron/Admin/form.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param int $id
     * @Route("/{id}/remove", name="effiana_cron_bundle_remove")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(?int $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $scheduledCommand = $em->getRepository('EffianaCronBundle:CronJob')->find($id);
        $em->remove($scheduledCommand);
        $em->flush($scheduledCommand);
        return $this->redirectToRoute('effiana_cron_bundle_index');
    }
}