/**
 * ツールチップと経過時間を表示する処理
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */
$(function () {
    $('[title]').each(function (e) {
        const timestamp = $(this).data('timestamp')
        const content = $(this).attr('title')
        if (timestamp) {
            $(this).attr('title', content + ' (' + passage(timestamp) + ')')
        }
        $(this).tooltip({ title: content })
    })

    // 経過時間を計算
    function passage (time) {
        const UNITS = { m: 60, h: 24, d: 1 }
        const UTIME = parseInt(new Date() / 1000)

        let passage = Math.max(0, (UTIME - time) / 60)
        let unit
        for (unit in UNITS) {
            if (passage < UNITS[unit]) {
                break
            }

            passage /= UNITS[unit]
        }
        return Math.floor(passage) + unit
    }
})
