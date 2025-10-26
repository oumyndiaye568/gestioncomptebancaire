<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compte;
use App\Models\Admin;
use App\Traits\ApiResponse;

/**
 * @OA\Info(
 *     title="API Gestion de Comptes Bancaires",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires avec authentification admin"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8001",
 *     description="Serveur de développement"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     name="Authorization",
 *     in="header",
 *     description="Token Bearer pour l'authentification"
 * )
 */

class AdminController extends Controller
{
    use ApiResponse;
    /**
     * Authentification de l'admin et génération du token
     *
     * @OA\Post(
     *     path="/api/admin/login",
     *     summary="Connexion administrateur",
     *     description="Authentifie un administrateur et retourne un token d'accès",
     *     operationId="adminLogin",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@test.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="admin", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="nom", type="string", example="Admin Test"),
     *                 @OA\Property(property="email", type="string", example="admin@test.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abc123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Échec d'authentification",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();
        if ($admin && \Hash::check($request->password, $admin->password)) {
            $token = $admin->createToken('admin-token')->plainTextToken;

            return $this->success([
                'admin' => $admin,
                'token' => $token,
            ], 'Connexion administrateur réussie');
        }

        return $this->unauthorized('Identifiants incorrects');
    }

    /**
     * Récupérer la liste des comptes avec filtrage, pagination, tri et recherche.
     *
     * @OA\Get(
     *     path="/api/admin/comptes",
     *     summary="Lister les comptes bancaires",
     *     description="Récupère la liste paginée des comptes avec possibilité de filtrage, tri et recherche",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de la page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte (cheque/epargne)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"cheque", "epargne"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut du compte (actif/inactif/bloque)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "inactif", "bloque"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom du titulaire",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="numeroCompte", type="string", example="COMP-20251025-EZ6TJPOP"),
     *                     @OA\Property(property="titulaire", type="string", example="Prof. Nicola Hessel"),
     *                     @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="epargne"),
     *                     @OA\Property(property="solde", type="number", format="float", example="452420.00"),
     *                     @OA\Property(property="devise", type="string", example="FCFA"),
     *                     @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-25T19:21:35+00:00"),
     *                     @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque"}, example="actif"),
     *                     @OA\Property(property="motifBlocage", type="string", nullable=true, example=null),
     *                     @OA\Property(property="metadata", type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=1),
     *                 @OA\Property(property="totalItems", type="integer", example=10),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=false),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="self", type="string", example="http://127.0.0.1:8001/api/admin/comptes"),
     *                 @OA\Property(property="next", type="string", nullable=true, example=null),
     *                 @OA\Property(property="first", type="string", example="http://127.0.0.1:8001/api/admin/comptes?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://127.0.0.1:8001/api/admin/comptes?page=1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getComptes(Request $request)
    {
        // Vérifier que l'utilisateur connecté est un Admin
        $user = $request->user();
        if (!$user instanceof Admin) {
            return $this->forbidden('Accès réservé aux administrateurs');
        }

        // Récupération des query parameters avec valeurs par défaut
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $type = $request->query('type');           // type de compte : epargne / cheque
        $statut = $request->query('statut');       // statut : actif / bloque / ferme
        $search = $request->query('search');       // recherche par titulaire ou numéro
        $sort = $request->query('sort', 'dateCreation'); // champ de tri
        $order = $request->query('order', 'asc');        // ordre de tri

        // Construction de la requête
        $query = Compte::with('client'); // Inclure le client lié à chaque compte

        // Filtrage par type
        if ($type) {
            $query->where('type_compte', $type);
        }

        // Filtrage par statut
        if ($statut) {
            $query->where('etat_compte', $statut);
        }

        // Recherche par titulaire ou numéro de compte
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('numero_compte', 'like', "%$search%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('nom_complet', 'like', "%$search%");
                  });
            });
        }

        // Tri
        switch ($sort) {
            case 'dateCreation':
                $query->orderBy('created_at', $order);
                break;

            case 'solde':
                $query->orderBy('solde', $order); // Assure-toi que le champ existe
                break;

            case 'titulaire':
                $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                      ->orderBy('clients.nom_complet', $order)
                      ->select('comptes.*'); // Evite les conflits
                break;

            default:
                $query->orderBy('created_at', $order);
        }

        // Pagination
        $comptes = $query->paginate($limit, ['*'], 'page', $page);

        // Formatage des données de réponse
        $data = $comptes->map(function($compte) {
            return [
                'id' => $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'titulaire' => $compte->client->nom_complet ?? null,
                'type' => $compte->type_compte,
                'solde' => $compte->solde ?? 0,
                'devise' => 'FCFA',
                'dateCreation' => $compte->created_at->toIso8601String(),
                'statut' => $compte->etat_compte,
                'motifBlocage' => $compte->motif_blocage ?? null,
                'metadata' => [
                    'derniereModification' => $compte->updated_at->toIso8601String(),
                    'version' => 1
                ]
            ];
        });

        // Retour avec le trait ApiResponse
        return $this->successWithPagination($data, [
            'currentPage' => $comptes->currentPage(),
            'totalPages' => $comptes->lastPage(),
            'totalItems' => $comptes->total(),
            'itemsPerPage' => $comptes->perPage(),
            'hasNext' => $comptes->hasMorePages(),
            'hasPrevious' => $comptes->currentPage() > 1,
            'links' => [
                'self' => $request->fullUrl(),
                'next' => $comptes->nextPageUrl(),
                'first' => $comptes->url(1),
                'last' => $comptes->url($comptes->lastPage())
            ]
        ], 'Liste des comptes récupérée avec succès');
    }
}
