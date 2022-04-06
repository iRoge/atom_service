<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Atom
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Atom create($value)
 * @package App
 * @property int $id
 * @property string|null $data_matrix_code
 * @property int $assortment_id
 * @property string|null $expired_at
 * @method static \Illuminate\Database\Eloquent\Builder|Atom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Atom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Atom query()
 * @method static \Illuminate\Database\Eloquent\Builder|Atom whereAssortmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Atom whereDataMatrixCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Atom whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Atom whereId($value)
 */
	class Atom extends \Eloquent {}
}

namespace App{
/**
 * App\AtomHistory
 *
 * @property int $id
 * @property int|null $id_parent
 * @property int $id_atom
 * @property int $id_admin
 * @property int $id_place
 * @property int|null $place_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $updated_at
 * @property-read AtomHistory|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereIdAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereIdAtom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereIdParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereIdPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory wherePlaceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtomHistory whereUpdatedAt($value)
 */
	class AtomHistory extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\Authenticatable, \Illuminate\Contracts\Auth\Access\Authorizable {}
}

